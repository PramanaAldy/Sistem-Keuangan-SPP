<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\FineSetting;
use App\Models\SppSetting;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $activeYear = AcademicYear::active();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $selectedYearId = $request->academic_year_id ?? $activeYear?->id;

        $students = Student::with(['schoolClass', 'transactions' => function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId)->with('details');
            }])
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->search, fn($q) => $q->where(fn($q2) => $q2->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('nisn', 'like', "%{$request->search}%")
                ->orWhere('parent_name', 'like', "%{$request->search}%")))
            ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
            ->where('status', 'active')
            ->orderBy('full_name')
            ->paginate(15)->withQueryString();

        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.transactions.index', compact(
            'students', 'classes', 'academicYears', 'activeYear', 'selectedYearId'
        ));
    }

    public function create(Request $request)
    {
        $activeYear = AcademicYear::active();
        $students = Student::with('schoolClass')
            ->where('status', 'active')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('full_name')->get();

        $selectedStudent = $request->student_id ? Student::find($request->student_id) : null;

        return view('admin.transactions.create', compact('activeYear', 'students', 'selectedStudent'));
    }

    /**
     * AJAX: Calculate SPP, fine, and discount for a student + months + payment_date
     */
    public function calculate(Request $request)
    {
        $student = Student::with(['schoolClass', 'discounts'])->findOrFail($request->student_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id ?? AcademicYear::active()?->id);
        $paymentDate = Carbon::parse($request->payment_date ?? today());
        $months = (array) $request->months;

        // Get SPP amount — di-cache 1 jam (setting jarang berubah)
        $sppAmount = 0;
        if (!$student->is_free_spp) {
            $sppSetting = Cache::remember(
                "spp_setting_{$student->class_id}_{$academicYear->id}",
                3600,
                fn() => SppSetting::where('class_id', $student->class_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->first()
            );
            $sppAmount = count($months) * ($sppSetting?->amount ?? 0);
        }

        // Get fine setting — di-cache 1 jam
        $fineAmount = 0;
        if (!$student->is_free_fine) {
            $fineSetting = Cache::remember(
                "fine_setting_{$academicYear->id}",
                3600,
                fn() => FineSetting::where('academic_year_id', $academicYear->id)->first()
            );
            if ($fineSetting) {
                foreach ($months as $month) {
                    $fineAmount += $fineSetting->calculateFine($paymentDate, (int)$month, (int)($request->year ?? date('Y')));
                }
            }
        }

        // Get discounts
        $discountAmount = 0;
        $activeDiscounts = $student->activeDiscounts($academicYear->id);
        foreach ($activeDiscounts as $discount) {
            $discountAmount += $discount->calculateDiscount($sppAmount);
        }
        $discountAmount = min($discountAmount, $sppAmount);

        $grandTotal = max(0, $sppAmount - $discountAmount + $fineAmount);

        // -- LOGIKA URUTAN & DP DATA (BARU) --
        $sequence = $academicYear->getMonthSequence();
        $paidDetails = TransactionDetail::whereHas('transaction', fn($q) =>
            $q->where('student_id', $student->id)->where('academic_year_id', $academicYear->id)
        )->get(['month', 'year']);

        $unpaidNeeds = [];
        // Map months to their respective years based on the sequence
        $selectedMonthsWithYears = [];
        foreach ($months as $m) {
            $matchingSeq = collect($sequence)->firstWhere('month', (int)$m);
            $selectedMonthsWithYears[] = [
                'month' => (int)$m, 
                'year' => $matchingSeq ? $matchingSeq['year'] : (int)$request->year
            ];
        }
        
        // Temukan titik kronologis TERBESAR dari pilihan user
        $lastSelectedIdx = null;
        foreach ($sequence as $idx => $s) {
            foreach ($selectedMonthsWithYears as $sel) {
                if ($s['month'] == $sel['month'] && $s['year'] == $sel['year']) {
                    if ($lastSelectedIdx === null || $idx > $lastSelectedIdx) {
                        $lastSelectedIdx = $idx;
                    }
                }
            }
        }

        // Cari lubang (gap) sebelum titik KETIGA / terakhir (semua bulan sebelum bulan maksimum yg dipilih)
        if ($lastSelectedIdx !== null) {
            for ($i = 0; $i < $lastSelectedIdx; $i++) {
                $check = $sequence[$i];
                $isPaid = $paidDetails->contains(fn($p) => $p->month == $check['month'] && $p->year == $check['year']);
                $isSelected = collect($selectedMonthsWithYears)->contains(fn($sel) => $sel['month'] == $check['month'] && $sel['year'] == $check['year']);
                
                if (!$isPaid && !$isSelected) {
                    $unpaidNeeds[] = $this->monthNames[$check['month']] . ' ' . $check['year'];
                }
            }
        }

        return response()->json([
            'spp_amount'      => $sppAmount,
            'fine_amount'     => $fineAmount,
            'discount_amount' => $discountAmount,
            'grand_total'     => $grandTotal,
            'unpaid_needed'   => $unpaidNeeds,
            'paid_months'     => $paidDetails, // Dikirim agar UI bisa mengunci (disabled)
            'sequence'        => $sequence,    // Dikirim agar UI bisa mengurutkan
            'discounts'       => $activeDiscounts->map(fn($d) => [
                'name'  => $d->name,
                'value' => $d->formatted_value,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'payment_date' => 'required|date',
            'months' => 'required|array|min:1',
            'months.*' => 'integer|min:1|max:12|distinct',
            'year' => 'required|integer',
            'spp_method' => 'required|in:cash,transfer,debit',
            'fine_method' => 'nullable|in:cash,transfer,debit',
            'notes' => 'nullable|string',
        ]);

        $student = Student::with('discounts')->findOrFail($request->student_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $paymentDate = Carbon::parse($request->payment_date);

        // Verify no duplicate months
        $sequence = $academicYear->getMonthSequence();
        $existing = false;
        foreach ($request->months as $m) {
            $seqItem = collect($sequence)->firstWhere('month', (int)$m);
            $actualYear = $seqItem ? $seqItem['year'] : $request->year;
            
            $exists = TransactionDetail::whereHas('transaction', fn($q) =>
                $q->where('student_id', $student->id)->where('academic_year_id', $academicYear->id)
            )->where('month', $m)->where('year', $actualYear)->exists();
            
            if ($exists) {
                $existing = true;
                break;
            }
        }

        if ($existing) {
            return back()->with('error', 'Beberapa bulan yang dipilih sudah pernah dibayar.')->withInput();
        }

        // -- VALIDASI URUTAN (BARU) --
        $calcResponse = $this->calculate(new Request([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'payment_date' => $request->payment_date,
            'months' => $request->months,
            'year' => $request->year,
        ]));
        $calc = json_decode($calcResponse->getContent(), true);

        if (!empty($calc['unpaid_needed'])) {
            $msg = "Siswa harus melunasi bulan: " . implode(', ', $calc['unpaid_needed']) . " sebelum membayar bulan ini.";
            return back()->with('error', $msg)->withInput();
        }

        $paidAmount = $calc['grand_total'];
        $proofPath = null;
        
        DB::transaction(function () use ($request, $student, $academicYear, $calc, $paymentDate, $proofPath, $paidAmount) {
            $transaction = Transaction::create([
                'receipt_number' => Transaction::generateReceiptNumber(),
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
                'admin_id' => Auth::guard('admin')->id(),
                'payment_date' => $paymentDate,
                'spp_amount' => $calc['spp_amount'],
                'fine_amount' => $calc['fine_amount'],
                'discount_amount' => $calc['discount_amount'],
                'grand_total' => $calc['grand_total'],
                'spp_method' => $request->spp_method,
                'fine_method' => $request->fine_method,
                'paid_amount' => $paidAmount,
                'proof_image' => $proofPath,
                'notes' => $request->notes,
            ]);

            $sequence = $academicYear->getMonthSequence();
            foreach ($request->months as $month) {
                // Cari tahun yang sesuai di sequence
                $seqItem = collect($sequence)->firstWhere('month', (int)$month);
                $actualYear = $seqItem ? $seqItem['year'] : $request->year;

                $transaction->details()->create([
                    'month' => $month,
                    'year' => $actualYear,
                ]);
            }
        });

        // Invalidate cache dashboard
        $yearId    = $request->academic_year_id;
        $thisYear  = now()->year;
        $todayDate = now()->format('Y_m_d');
        Cache::forget("dashboard_stats_v3_{$yearId}_{$todayDate}");
        Cache::forget("dashboard_chart_{$yearId}_{$thisYear}");

        $transaction = Transaction::with('student')
            ->where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->latest()->first();

        // Audit log
        AuditLog::record('created', $transaction,
            "Transaksi {$transaction->receipt_number} — {$transaction->student->full_name} — Rp " .
            number_format($transaction->grand_total, 0, ',', '.')
        );

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil disimpan.')
            ->with('show_receipt', true);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['student.schoolClass', 'details', 'admin', 'academicYear']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $transaction->load(['student.schoolClass', 'details']);
        return view('admin.transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'payment_date'     => 'sometimes|required|date',
            'spp_method'       => 'sometimes|required|in:cash,transfer,debit',
            'fine_method'      => 'nullable|in:cash,transfer,debit',
            'notes'            => 'nullable|string',
            'transfer_nominal' => 'nullable|numeric',
        ]);

        $old = $transaction->only('payment_date', 'spp_method', 'fine_method', 'notes', 'transfer_nominal');

        $updateData = [];
        if ($request->has('payment_date')) {
            $updateData['payment_date'] = $request->payment_date;
        }
        if ($request->has('spp_method')) {
            $updateData['spp_method'] = $request->spp_method;
        }
        if ($request->has('fine_method')) {
            $updateData['fine_method'] = $request->fine_method;
        }
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }
        if ($request->has('transfer_nominal')) {
            $updateData['transfer_nominal'] = $request->transfer_nominal;
        }

        if (!empty($updateData)) {
            $transaction->update($updateData);
        }

        // Invalidate dashboard caches for both old and new dates if payment_date changed
        $yearId = $transaction->academic_year_id;
        if (isset($old['payment_date'])) {
            $oldDate = Carbon::parse($old['payment_date']);
            $newDate = Carbon::parse($transaction->payment_date);

            Cache::forget("dashboard_stats_v3_{$yearId}_" . $oldDate->format('Y_m_d'));
            Cache::forget("dashboard_stats_v3_{$yearId}_" . $newDate->format('Y_m_d'));
            Cache::forget("dashboard_chart_{$yearId}_" . $oldDate->year);
            Cache::forget("dashboard_chart_{$yearId}_" . $newDate->year);
        }

        // Audit log
        AuditLog::record('updated', $transaction,
            "Edit transaksi {$transaction->receipt_number}",
            $old,
            $transaction->only('payment_date', 'spp_method', 'fine_method', 'notes', 'transfer_nominal')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Data berhasil diperbarui.',
                'transfer_nominal' => $transaction->transfer_nominal,
                'formatted_date' => Carbon::parse($transaction->payment_date)->locale('id')->isoFormat('dddd, D MMM YYYY')
            ]);
        }

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        $yearId    = $transaction->academic_year_id;
        $thisYear  = now()->year;

        // Audit log sebelum dihapus (setelah hapus tidak bisa akses lagi)
        $transaction->load('student');
        AuditLog::record('deleted', $transaction,
            "Hapus transaksi {$transaction->receipt_number} — {$transaction->student?->full_name} — Rp " .
            number_format((float) $transaction->grand_total, 0, ',', '.')
        );

        $transaction->details()->delete();
        $transaction->delete();

        $todayDate = now()->format('Y_m_d');
        Cache::forget("dashboard_stats_v3_{$yearId}_{$todayDate}");
        Cache::forget("dashboard_chart_{$yearId}_{$thisYear}");
        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function destroyDetail(TransactionDetail $detail)
    {
        // Simpan semua data yang dibutuhkan SEBELUM dihapus
        $detail->load('transaction.student');
        $transaction  = $detail->transaction;
        $monthName    = $detail->month_name;
        $detailYear   = $detail->year;
        $yearId       = $transaction->academic_year_id;
        $payYear      = $transaction->payment_date->year;
        $receiptNo    = $transaction->receipt_number;

        DB::transaction(function () use ($detail, $transaction, $monthName, $detailYear, $receiptNo) {
            $detail->delete();

            $remainingCount = $transaction->details()->count();

            // Jika tidak ada detail tersisa, hapus transaksi induk juga
            if ($remainingCount === 0) {
                AuditLog::record('deleted', $transaction,
                    "Hapus transaksi {$receiptNo} (detail terakhir: {$monthName} {$detailYear})"
                );
                $transaction->delete();
            } else {
                // --- FIX #1: Recalculate nominal induk transaksi ---
                // Distribusikan kembali SPP & discount secara proporsional dari sisa bulan
                $originalMonthCount = $remainingCount + 1; // sebelum hapus
                $perMonthSpp      = $originalMonthCount > 0
                    ? round($transaction->spp_amount / $originalMonthCount, 2)
                    : 0;
                $perMonthDiscount = $originalMonthCount > 0
                    ? round($transaction->discount_amount / $originalMonthCount, 2)
                    : 0;

                $newSppAmount      = round($perMonthSpp * $remainingCount, 2);
                $newDiscountAmount = round($perMonthDiscount * $remainingCount, 2);

                // Denda: recalculate dari FineSetting berdasarkan sisa bulan
                $newFineAmount = 0;
                $academicYear  = $transaction->academicYear;
                if ($academicYear && !$transaction->student->is_free_fine) {
                    $fineSetting = FineSetting::where('academic_year_id', $academicYear->id)->first();
                    if ($fineSetting) {
                        $paymentDate = Carbon::parse($transaction->payment_date);
                        foreach ($transaction->details as $d) {
                            $newFineAmount += $fineSetting->calculateFine($paymentDate, (int)$d->month, (int)$d->year);
                        }
                    }
                }

                $newGrandTotal = max(0, $newSppAmount - $newDiscountAmount + $newFineAmount);

                $transaction->update([
                    'spp_amount'      => $newSppAmount,
                    'discount_amount' => $newDiscountAmount,
                    'fine_amount'     => $newFineAmount,
                    'grand_total'     => $newGrandTotal,
                    'paid_amount'     => $newGrandTotal,
                ]);

                AuditLog::record('updated', $transaction,
                    "Hapus bulan {$monthName} {$detailYear} dari transaksi {$receiptNo}. Grand total disesuaikan menjadi Rp " .
                    number_format($newGrandTotal, 0, ',', '.')
                );
            }
        });

        // Invalidate dashboard cache (menggunakan nilai yang sudah disimpan sebelum delete)
        $todayDate = now()->format('Y_m_d');
        Cache::forget("dashboard_stats_v3_{$yearId}_{$todayDate}");
        Cache::forget("dashboard_chart_{$yearId}_{$payYear}");

        return redirect()->route('admin.transactions.index')
            ->with('success', "Bulan {$monthName} {$detailYear} berhasil dihapus dan nominal transaksi telah disesuaikan.");
    }

    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['student.schoolClass', 'details', 'admin', 'academicYear']);
        $pdf = Pdf::loadView('admin.transactions.receipt', compact('transaction'))->setPaper('a4', 'landscape');
        return $pdf->stream('kwitansi-' . $transaction->receipt_number . '.pdf');
    }
}
