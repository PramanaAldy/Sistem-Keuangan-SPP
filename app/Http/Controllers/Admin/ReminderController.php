<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FineSetting;
use App\Models\SppSetting;
use App\Models\Student;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReminderController extends Controller
{
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $activeYear = AcademicYear::active();
        if (!$activeYear) {
            return view('admin.reminders.index', [
                'groups'     => collect(),
                'activeYear' => null,
                'classes'    => collect(),
                'monthNames' => $this->monthNames,
            ]);
        }

        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $fineSetting  = FineSetting::where('academic_year_id', $activeYear->id)->first();

        // Sequence bulan sesuai tahun ajaran: Jul(startYear)...Jun(endYear)
        $sequence = $activeYear->getMonthSequence();
        $now      = Carbon::now();

        // Batasi hanya sampai bulan & tahun sekarang
        $validSequence = array_values(array_filter($sequence, function ($s) use ($now) {
            return Carbon::create($s['year'], $s['month'], 1)->lte($now->copy()->startOfMonth());
        }));

        // Pre-load semua paid details (tanpa batasan year) untuk tahun ajaran ini
        $allPaidDetails = TransactionDetail::whereHas('transaction', fn($q) =>
            $q->where('academic_year_id', $activeYear->id)
        )
            ->with('transaction:id,student_id')
            ->get(['month', 'year', 'transaction_id'])
            ->groupBy(fn($d) => $d->transaction->student_id);

        // 1. Map each student → attach arrear data (or null if no arrears)
        $students = Student::with(['schoolClass'])
            ->where('academic_year_id', $activeYear->id)
            ->where('status', 'active')
            ->where('is_free_spp', false)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->orderBy('full_name')
            ->get()
            ->map(function (Student $student) use ($validSequence, $activeYear, $fineSetting, $allPaidDetails, $now, $currentYear, $currentMonth) {
                $paidDetails = $allPaidDetails->get($student->id, collect());

                // Cari bulan yang belum dibayar dalam sequence tahun ajaran
                $arrearMonths = [];
                foreach ($validSequence as $s) {
                    $alreadyPaid = $paidDetails->contains(
                        fn($p) => $p->month == $s['month'] && $p->year == $s['year']
                    );
                    if (!$alreadyPaid) {
                        $arrearMonths[] = $s; // ['month'=>X, 'year'=>Y]
                    }
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
                $student->loadMissing('discounts');
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

        // 2. Group by normalised WhatsApp number
        //    Students without WA get a unique key so they stay as individual rows
        $groups = $students
            ->groupBy(function (Student $s) {
                if (!$s->whatsapp) return 'no_wa_' . $s->id;
                $phone = preg_replace('/[^0-9]/', '', $s->whatsapp);
                return str_starts_with($phone, '0') ? '62' . substr($phone, 1) : $phone;
            })
            ->map(function (Collection $siblings) use ($activeYear) {
                $phone      = $siblings->first()->whatsapp;
                $normalised = '';
                if ($phone) {
                    $normalised = preg_replace('/[^0-9]/', '', $phone);
                    if (str_starts_with($normalised, '0')) $normalised = '62' . substr($normalised, 1);
                }
                $totalGrand = $siblings->sum('grand_total');
                $isSibling  = $siblings->count() > 1;
                $message    = $isSibling
                    ? $this->buildCombinedMessage($siblings, $activeYear->name)
                    : $this->buildSingleMessage($siblings->first(), $activeYear->name);

                return [
                    'phone'      => $normalised,
                    'raw_phone'  => $phone,
                    'students'   => $siblings->values(),
                    'grand_total'=> $totalGrand,
                    'is_sibling' => $isSibling,
                    'wa_url'     => $normalised
                        ? 'https://wa.me/' . $normalised . '?text=' . rawurlencode($message)
                        : null,
                ];
            })
            ->values();

        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.reminders.index', compact('groups', 'activeYear', 'classes') + [
            'monthNames' => $this->monthNames,
        ]);
    }

    // ─── Single student message ───────────────────────────────────────────────
    private function buildSingleMessage(Student $student, string $yearName): string
    {
        $months = [];
        foreach ($student->arrear_months as $arrear) {
            // Support both old (int) and new (['month'=>X, 'year'=>Y]) format
            if (is_array($arrear)) {
                $months[] = ucfirst(strtolower($this->monthNames[$arrear['month']])) . ' ' . $arrear['year'];
            } else {
                $months[] = ucfirst(strtolower($this->monthNames[$arrear]));
            }
        }
        $monthStr = implode(', ', $months);
        $sppTotalStr   = "Rp " . number_format($student->total_spp - $student->discount_amount, 0, ',', '.');
        $dendaStr      = "Rp " . number_format($student->fine_amount, 0, ',', '.');
        $grandTotalStr = "Rp " . number_format($student->grand_total, 0, ',', '.');
        $namaAnak = $student->full_name;
        $kelas    = $student->schoolClass->display_name ?? '-';

        $body = "Yth. Bapak/Ibu Orang Tua {$namaAnak},\n\n"
              . "Pembayaran SPP TERLAMBAT:\n\n"
              . "1. {$namaAnak} ({$kelas})\n"
              . "   - Bulan: {$monthStr}\n"
              . "     total: {$sppTotalStr}\n"
              . "     Denda: {$dendaStr}\n"
              . "     Grand Total: {$grandTotalStr}\n\n";

        $bank   = 'BCA';
        $rek    = '7670321988';
        $an     = 'Bunny Gawah';
        $school = 'Universal School';

        $body .= "Pembayaran dapat dilakukan:\n"
              . "1. Tunai atau Debet\n"
              . "2. Transfer ke:\n"
              . "   {$bank}\n"
              . "   No. Rek: {$rek}\n"
              . "   a/n {$an}\n"
              . "WAJIB DI ISI NAMA dan KELAS PADA BERITA\n\n"
              . "Mohon segera melakukan pembayaran dan kirim bukti transfer.\n"
              . "Jika tidak melakukan pembayaran maka denda akan terakumulasi Rp 3.000/hari\n\n"
              . "Terima kasih.\n"
              . "{$school}";

        return $body;
    }

    // ─── Combined sibling message ─────────────────────────────────────────────
    private function buildCombinedMessage(Collection $siblings, string $yearName): string
    {
        $firstStudent = $siblings->first();
        $parentName = $firstStudent->parent_name ?: $firstStudent->full_name . ' dkk';

        $body = "Yth. Bapak/Ibu Orang Tua {$parentName},\n\n"
              . "Pembayaran SPP TERLAMBAT:\n\n";

        foreach ($siblings as $idx => $student) {
            $months = [];
            foreach ($student->arrear_months as $arrear) {
                if (is_array($arrear)) {
                    $months[] = ucfirst(strtolower($this->monthNames[$arrear['month']])) . ' ' . $arrear['year'];
                } else {
                    $months[] = ucfirst(strtolower($this->monthNames[$arrear]));
                }
            }
            $monthStr = implode(', ', $months);
            $sppTotalStr   = "Rp " . number_format($student->total_spp - $student->discount_amount, 0, ',', '.');
            $dendaStr      = "Rp " . number_format($student->fine_amount, 0, ',', '.');
            $grandTotalStr = "Rp " . number_format($student->grand_total, 0, ',', '.');
            $namaAnak = $student->full_name;
            $kelas    = $student->schoolClass->display_name ?? '-';

            $no = $idx + 1;
            $body .= "{$no}. {$namaAnak} ({$kelas})\n"
                  . "   - Bulan: {$monthStr}\n"
                  . "     total: {$sppTotalStr}\n"
                  . "     Denda: {$dendaStr}\n"
                  . "     Grand Total: {$grandTotalStr}\n\n";
        }

        if ($siblings->count() > 1) {
            $totalGrand = "Rp " . number_format($siblings->sum('grand_total'), 0, ',', '.');
            $body .= "TOTAL KESELURUHAN: *{$totalGrand}*\n\n";
        }

        $bank   = 'BCA';
        $rek    = '7670321988';
        $an     = 'Bunny Gawah';
        $school = 'Universal School';

        $body .= "Pembayaran dapat dilakukan:\n"
              . "1. Tunai atau Debet\n"
              . "2. Transfer ke:\n"
              . "   {$bank}\n"
              . "   No. Rek: {$rek}\n"
              . "   a/n {$an}\n"
              . "WAJIB DI ISI NAMA dan KELAS PADA BERITA\n\n"
              . "Mohon segera melakukan pembayaran dan kirim bukti transfer.\n"
              . "Jika tidak melakukan pembayaran maka denda akan terakumulasi Rp 3.000/hari\n\n"
              . "Terima kasih.\n"
              . "{$school}";

        return $body;
    }

    public function whatsapp(Student $student, Request $request)
    {
        $message = urlencode($request->message ?? '');
        $phone   = preg_replace('/[^0-9]/', '', $student->whatsapp ?? '');
        if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
        return redirect("https://wa.me/{$phone}?text={$message}");
    }
}
