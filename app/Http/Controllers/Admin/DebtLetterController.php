<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FineSetting;
use App\Models\SppSetting;
use App\Models\Student;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DebtLetterController extends Controller
{
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Hitung bulan tunggakan berdasarkan urutan tahun ajaran (Juli s/d bulan sekarang)
     * Return array of ['month'=>X, 'year'=>Y]
     */
    private function getArrearMonths(Student $student, AcademicYear $activeYear): array
    {
        $sequence = $activeYear->getMonthSequence();
        $now      = Carbon::now();

        $validSequence = array_values(array_filter($sequence, function ($s) use ($now) {
            return Carbon::create($s['year'], $s['month'], 1)->lte($now->copy()->startOfMonth());
        }));

        $paidDetails = TransactionDetail::whereHas('transaction', fn($q) =>
            $q->where('student_id', $student->id)->where('academic_year_id', $activeYear->id)
        )->get(['month', 'year']);

        $arrears = [];
        foreach ($validSequence as $s) {
            $isPaid = $paidDetails->contains(fn($p) => $p->month == $s['month'] && $p->year == $s['year']);
            if (!$isPaid) {
                $arrears[] = $s;
            }
        }
        return $arrears;
    }

    public function index(Request $request)
    {
        $activeYear = AcademicYear::active();
        if (!$activeYear) {
            return view('admin.debt-letters.index', [
                'students'   => collect(),
                'activeYear' => null,
                'classes'    => collect(),
            ]);
        }

        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $fineSetting  = FineSetting::where('academic_year_id', $activeYear->id)->first();

        // Pre-load semua paid details (tanpa batasan year) untuk tahun ajaran ini
        $sequence = $activeYear->getMonthSequence();
        $now      = Carbon::now();
        $validSequence = array_values(array_filter($sequence, function ($s) use ($now) {
            return Carbon::create($s['year'], $s['month'], 1)->lte($now->copy()->startOfMonth());
        }));

        $allPaidDetails = \App\Models\TransactionDetail::whereHas('transaction', fn($q) =>
            $q->where('academic_year_id', $activeYear->id)
        )
            ->with('transaction:id,student_id')
            ->get(['month', 'year', 'transaction_id'])
            ->groupBy(fn($d) => $d->transaction->student_id);

        $students = Student::with(['schoolClass', 'discounts'])
            ->where('academic_year_id', $activeYear->id)
            ->where('status', 'active')
            ->where('is_free_spp', false)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->orderBy('full_name')
            ->get()
            ->map(function ($student) use ($validSequence, $activeYear, $fineSetting, $allPaidDetails, $currentYear, $currentMonth) {
                $paidDetails = $allPaidDetails->get($student->id, collect());

                $arrearMonths = [];
                foreach ($validSequence as $s) {
                    $isPaid = $paidDetails->contains(fn($p) => $p->month == $s['month'] && $p->year == $s['year']);
                    if (!$isPaid) $arrearMonths[] = $s;
                }

                if (empty($arrearMonths)) return null;

                $sppSetting  = SppSetting::where('class_id', $student->class_id)
                    ->where('academic_year_id', $activeYear->id)->first();
                $sppPerMonth = $sppSetting?->amount ?? 0;
                $totalSpp    = count($arrearMonths) * $sppPerMonth;

                $fineAmount = 0;
                if ($fineSetting && !$student->is_free_fine) {
                    foreach ($arrearMonths as $arrear) {
                        $fineAmount += $fineSetting->calculateFine(Carbon::today(), $arrear['month'], $arrear['year']);
                    }
                }

                $discountAmount  = 0;
                $activeDiscounts = $student->activeDiscounts($activeYear->id);
                foreach ($activeDiscounts as $discount) {
                    $discountAmount += $discount->calculateDiscount($totalSpp);
                }
                $discountAmount = min($discountAmount, $totalSpp);
                $grandTotal     = max(0, $totalSpp - $discountAmount + $fineAmount);

                $student->arrear_months   = $arrearMonths;
                $student->spp_per_month   = $sppPerMonth;
                $student->total_spp       = $totalSpp;
                $student->fine_amount     = $fineAmount;
                $student->discount_amount = $discountAmount;
                $student->grand_total     = $grandTotal;
                return $student;
            })
            ->filter()
            ->values();

        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.debt-letters.index', compact('students', 'activeYear', 'classes'));
    }

    public function show(Student $student, Request $request)
    {
        $activeYear   = AcademicYear::active();
        if (!$activeYear) {
            return redirect()->route('admin.debt-letters.index')
                ->with('error', 'Tidak ada tahun ajaran yang aktif. Silakan aktifkan tahun ajaran terlebih dahulu.');
        }
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $fineSetting  = FineSetting::where('academic_year_id', $activeYear->id)->first();

        $student->load(['schoolClass', 'discounts']);

        // Gunakan sequence tahun ajaran
        $arrearMonths = $this->getArrearMonths($student, $activeYear);

        $sppSetting   = SppSetting::where('class_id', $student->class_id)
            ->where('academic_year_id', $activeYear->id)->first();
        $sppPerMonth  = $sppSetting?->amount ?? 0;
        $totalSpp     = count($arrearMonths) * $sppPerMonth;

        $fineAmount = 0;
        if ($fineSetting && !$student->is_free_fine) {
            foreach ($arrearMonths as $arrear) {
                $fineAmount += $fineSetting->calculateFine(Carbon::today(), $arrear['month'], $arrear['year']);
            }
        }

        $discountAmount  = 0;
        $activeDiscounts = $student->activeDiscounts($activeYear->id);
        foreach ($activeDiscounts as $discount) {
            $discountAmount += $discount->calculateDiscount($totalSpp);
        }
        $discountAmount = min($discountAmount, $totalSpp);
        $grandTotal     = max(0, $totalSpp - $discountAmount + $fineAmount);

        $monthNames = $this->monthNames;

        // Siapkan URL WhatsApp di server (bukan di JavaScript, agar lebih andal)
        $waPhone = '';
        $waUrl   = null;
        if ($student->whatsapp) {
            $waPhone = preg_replace('/[^0-9]/', '', $student->whatsapp);
            if (str_starts_with($waPhone, '0')) {
                $waPhone = '62' . substr($waPhone, 1);
            }
            $waMessage = "Yth. Bapak/Ibu " . ($student->parent_name ?: 'Orang Tua') . ",\n\n"
                . "Bersama pesan ini kami sampaikan Surat Pemberitahuan Tunggakan Pembayaran SPP untuk putra/putri Bapak/Ibu:\n\n"
                . "Nama  : " . $student->full_name . "\n"
                . "Kelas : " . (optional($student->schoolClass)->display_name ?? '-') . "\n"
                . "Total : Rp " . number_format($grandTotal, 0, ',', '.') . "\n\n"
                . "Surat tunggakan resmi kami lampirkan dalam pesan ini.\n"
                . "Mohon segera melakukan pembayaran paling lambat akhir bulan ini.\n\n"
                . "Terima kasih atas perhatian Bapak/Ibu.\n"
                . "Universal School";
            $waUrl = 'https://api.whatsapp.com/send?phone=' . $waPhone . '&text=' . rawurlencode($waMessage);
        }

        return view('admin.debt-letters.show', compact(
            'student', 'activeYear', 'arrearMonths', 'sppPerMonth',
            'totalSpp', 'fineAmount', 'discountAmount', 'grandTotal', 'monthNames',
            'waUrl', 'fineSetting'
        ));
    }
    public function downloadPdf(Student $student): \Symfony\Component\HttpFoundation\Response
    {
        $activeYear   = AcademicYear::active();
        if (!$activeYear) {
            abort(404, 'Tahun ajaran aktif tidak ditemukan.');
        }
        $fineSetting  = FineSetting::where('academic_year_id', $activeYear->id)->first();

        $student->load(['schoolClass', 'discounts']);

        // Gunakan sequence tahun ajaran
        $arrearMonths = $this->getArrearMonths($student, $activeYear);

        $sppSetting  = SppSetting::where('class_id', $student->class_id)
            ->where('academic_year_id', $activeYear->id)->first();
        $sppPerMonth = $sppSetting?->amount ?? 0;
        $totalSpp    = count($arrearMonths) * $sppPerMonth;

        $fineAmount = 0;
        if ($fineSetting && !$student->is_free_fine) {
            foreach ($arrearMonths as $arrear) {
                $fineAmount += $fineSetting->calculateFine(Carbon::today(), $arrear['month'], $arrear['year']);
            }
        }

        $discountAmount  = 0;
        $activeDiscounts = $student->activeDiscounts($activeYear->id);
        foreach ($activeDiscounts as $discount) {
            $discountAmount += $discount->calculateDiscount($totalSpp);
        }
        $discountAmount = min($discountAmount, $totalSpp);
        $grandTotal     = max(0, $totalSpp - $discountAmount + $fineAmount);
        $monthNames     = $this->monthNames;

        $pdf = Pdf::loadView('admin.debt-letters.pdf', compact(
            'student', 'activeYear', 'arrearMonths', 'sppPerMonth',
            'totalSpp', 'fineAmount', 'discountAmount', 'grandTotal', 'monthNames', 'fineSetting'
        ))->setPaper('a4', 'portrait');

        $filename = 'Surat-Tunggakan-' . str_replace(' ', '-', $student->full_name) . '-' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
