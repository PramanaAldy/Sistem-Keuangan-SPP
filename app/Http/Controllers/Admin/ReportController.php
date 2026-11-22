<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function daily(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $activeYear = AcademicYear::active();

        $transactions = Transaction::with(['student.schoolClass', 'details'])
            ->whereDate('payment_date', $date)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('payment_date')
            ->get();

        $totalCash     = $transactions->sum(fn($t) => $t->spp_method === 'cash'     ? ($t->paid_amount ?? $t->grand_total) : 0);
        $totalTransfer = $transactions->sum(fn($t) => $t->spp_method === 'transfer' ? ($t->paid_amount ?? $t->grand_total) : 0);
        $totalDebit    = $transactions->sum(fn($t) => $t->spp_method === 'debit'    ? ($t->paid_amount ?? $t->grand_total) : 0);
        $grandTotal    = $transactions->sum(fn($t) => $t->paid_amount ?? $t->grand_total);

        if ($request->action === 'print') {
            return view('admin.reports.daily-print', compact('transactions', 'date', 'totalCash', 'totalTransfer', 'totalDebit', 'grandTotal'));
        }

        return view('admin.reports.daily', compact('transactions', 'date', 'totalCash', 'totalTransfer', 'totalDebit', 'grandTotal'));
    }

    public function monthly(Request $request)
    {
        $month = (int) ($request->month ?? Carbon::now()->month);
        $year = (int) ($request->year ?? Carbon::now()->year);
        $activeYear = AcademicYear::active();

        // 1. Get Opening Balance (Total collected before current month/year in the same academic year)
        $openingBalance = 0;
        if ($activeYear) {
            $startDate = Carbon::create($activeYear->start_year, 7, 1)->startOfDay();
            $currentPeriodStart = Carbon::create($year, $month, 1)->startOfDay();
            
            if ($currentPeriodStart->gt($startDate)) {
                $openingBalance = Transaction::where('academic_year_id', $activeYear->id)
                    ->where('payment_date', '<', $currentPeriodStart)
                    ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(paid_amount, grand_total)'));
            }
        }

        // 2. Query transactions for the current month
        $transactions = Transaction::with(['student', 'details'])
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('payment_date')
            ->get();

        // 3. Aggregate daily
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $dailyData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dailyData[$i] = [
                'day' => $i,
                'date' => Carbon::create($year, $month, $i)->format('Y-m-d'),
                'jumlah_anak' => 0,
                'total_anak' => 0,
                'debet' => 0,
                'saldo' => 0,
                'denda' => 0,
                'notes' => [],
                'months_paid' => [
                    7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0
                ]
            ];
        }

        foreach ($transactions as $tx) {
            $day = Carbon::parse($tx->payment_date)->day;
            // DEBET di sini diformat sebagai SPP bersih (uang masuk dikurangi denda), sesuai permintaan view
            $txPaid  = $tx->paid_amount ?? $tx->grand_total;
            $txDebet = $txPaid - $tx->fine_amount;

            $dailyData[$day]['jumlah_anak']++;
            $dailyData[$day]['debet'] += $txDebet;
            $dailyData[$day]['denda'] += $tx->fine_amount;

            if ($tx->notes && !in_array($tx->notes, $dailyData[$day]['notes'])) {
                $dailyData[$day]['notes'][] = $tx->notes;
            }

            // FIX #2: Distribusi nominal per bulan menggunakan tarif SPP per kelas
            // bukan dibagi rata, agar lebih akurat jika tarif per bulan berbeda
            $details      = $tx->details;
            $detailsCount = $details->count();

            if ($detailsCount > 0) {
                // Coba ambil tarif per kelas dari SppSetting
                $sppPerClass = $activeYear
                    ? \App\Models\SppSetting::where('academic_year_id', $activeYear->id)
                        ->where('class_id', $tx->student->class_id ?? 0)
                        ->value('amount')
                    : null;

                // Jika tarif per kelas tidak ditemukan, distribusikan merata sebagai fallback
                $sppNetPerMonth = $sppPerClass !== null
                    ? (float)$sppPerClass
                    : ($tx->spp_amount - $tx->discount_amount) / $detailsCount;

                foreach ($details as $d) {
                    $m = $d->month;
                    if (isset($dailyData[$day]['months_paid'][$m])) {
                        $dailyData[$day]['months_paid'][$m] += $sppNetPerMonth;
                    }
                }
            }
        }

        // 4. Filter and calculate running totals
        $filteredDailyData = [];
        $runningSaldo = $openingBalance;
        $runningStudents = 0;

        foreach ($dailyData as $day => $d) {
            if ($d['jumlah_anak'] > 0 || $d['debet'] > 0 || $d['denda'] > 0) {
                $runningStudents += $d['jumlah_anak'];
                $runningSaldo   += ($d['debet'] + $d['denda']);
                
                $d['total_anak'] = $runningStudents;
                $d['saldo']      = $runningSaldo;
                
                $filteredDailyData[] = $d;
            }
        }

        $summary = [
            'total_debet'     => array_sum(array_column($filteredDailyData, 'debet')),
            'total_denda'     => array_sum(array_column($filteredDailyData, 'denda')),
            'grand_total'     => 0,
            'opening_balance' => $openingBalance,
            'final_balance'   => 0,
            'target_spp'      => 0,
            'total_per_month' => []
        ];

        $sequence = [7,8,9,10,11,12,1,2,3,4,5,6];
        foreach ($sequence as $m) {
            $summary['total_per_month'][$m] = array_sum(array_map(function($day) use ($m) {
                return $day['months_paid'][$m] ?? 0;
            }, $filteredDailyData));
        }
        // GRAND TOTAL = TOTAL (Debet) + DENDA
        $summary['grand_total']   = $summary['total_debet'] + $summary['total_denda'];
        $summary['final_balance'] = $summary['opening_balance'] + $summary['grand_total'];

        // Calculate Target SPP (BALANCE)
        if ($activeYear) {
            $activeStudents = Student::where('status', 'active')
                ->where('academic_year_id', $activeYear->id)
                ->where('is_free_spp', false)
                ->get(['class_id']);
                
            $sppSettings = \App\Models\SppSetting::where('academic_year_id', $activeYear->id)
                ->get()
                ->keyBy('class_id');
                
            foreach ($activeStudents as $student) {
                if (isset($sppSettings[$student->class_id])) {
                    $summary['target_spp'] += $sppSettings[$student->class_id]->amount;
                }
            }
        }
        $summary['selisih'] = $summary['target_spp'] - $summary['grand_total'];

        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $shortMonthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        if ($request->action === 'print') {
            return view('admin.reports.monthly-print', compact('filteredDailyData', 'summary', 'month', 'year', 'monthNames', 'shortMonthNames', 'sequence', 'activeYear'));
        }

        return view('admin.reports.monthly', compact('filteredDailyData', 'summary', 'month', 'year', 'monthNames', 'shortMonthNames', 'sequence', 'activeYear'));
    }

    public function recap(Request $request)
    {
        $month = (int) ($request->month ?? Carbon::now()->month);
        $year = (int) ($request->year ?? Carbon::now()->year);
        $activeYear = AcademicYear::active();
        $yearId = $activeYear ? $activeYear->id : null;

        // 1. Pesdik Grouping
        $allStudents = Student::with('schoolClass')
            ->where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->get();

        $pesdik = [
            'tk' => 0,
            'sd' => 0,
            'smp' => 0,
            'total' => $allStudents->count(),
        ];

        foreach ($allStudents as $student) {
            $className = strtolower(optional($student->schoolClass)->display_name ?? '');
            if (str_contains($className, 'pg') || str_contains($className, 'tk')) {
                $pesdik['tk']++;
            } elseif (str_contains($className, 'smp') || preg_match('/(7|8|9)/', $className)) {
                $pesdik['smp']++;
            } else {
                $pesdik['sd']++; 
            }
        }

        // 2. Segmentation
        $freeCount = Student::where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->where('is_free_spp', true)
            ->count();

        // FIX #6: Pastikan khususCount tidak overlap dengan freeCount
        // Siswa khusus = yang BUKAN bebas SPP tapi punya diskon atau saudara
        $khususCount = Student::where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->where('is_free_spp', false)  // Pastikan tidak overlap dengan freeCount
            ->where(function ($q) use ($yearId) {
                $q->whereHas('discounts', function ($q2) use ($yearId) {
                    $q2->where('student_discounts.academic_year_id', $yearId);
                })->orWhereHas('siblings');
            })
            ->count();

        // FIX #11: Lindungi targetPembayar agar tidak negatif
        $targetPembayar = max(0, $pesdik['total'] - $freeCount - $khususCount);

        // 3. Transactions Received — pisahkan SPP dan Denda
        $txQuery = Transaction::whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId));

        $totalCollected = (clone $txQuery)->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(paid_amount, grand_total)'));
        $totalSpp       = (clone $txQuery)->sum('spp_amount');
        $totalDiscount  = (clone $txQuery)->sum('discount_amount');
        $totalFine      = (clone $txQuery)->sum('fine_amount');
        $totalSppNet    = $totalSpp - $totalDiscount; // SPP setelah diskon

        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        if ($request->action === 'print') {
            return view('admin.reports.recap-print', compact('pesdik', 'freeCount', 'khususCount', 'targetPembayar', 'totalCollected', 'totalSppNet', 'totalFine', 'month', 'year', 'monthNames'));
        }

        return view('admin.reports.recap', compact('pesdik', 'freeCount', 'khususCount', 'targetPembayar', 'totalCollected', 'totalSppNet', 'totalFine', 'month', 'year', 'monthNames'));
    }

    public function yearly(Request $request)
    {
        $year = (int) ($request->year ?? Carbon::now()->year);
        $activeYear = AcademicYear::active();

        // Satu query dengan GROUP BY — jauh lebih efisien dari 12 query terpisah
        $rawData = Transaction::selectRaw('MONTH(payment_date) as month, spp_method, SUM(COALESCE(paid_amount, grand_total)) as total, COUNT(*) as cnt')
            ->whereYear('payment_date', $year)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->groupByRaw('MONTH(payment_date), spp_method')
            ->get()
            ->groupBy('month');

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows = $rawData->get($m, collect());
            $monthlyData[$m] = [
                'total'    => (float) $rows->sum('total'),
                'count'    => (int)   $rows->sum('cnt'),
                'cash'     => (float) $rows->where('spp_method', 'cash')->sum('total'),
                'transfer' => (float) $rows->where('spp_method', 'transfer')->sum('total'),
                'debit'    => (float) $rows->where('spp_method', 'debit')->sum('total'),
            ];
        }

        $yearTotal = array_sum(array_column($monthlyData, 'total'));
        $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        return view('admin.reports.yearly', compact('monthlyData', 'year', 'yearTotal', 'monthNames'));
    }

    public function arrears(Request $request)
    {
        $activeYear = AcademicYear::active();
        if (!$activeYear) {
            return view('admin.reports.arrears', ['students' => collect(), 'activeYear' => null, 'classes' => collect(), 'monthNames' => []]);
        }

        // Sequence bulan sesuai tahun ajaran: Jul(startYear)...Des(startYear), Jan(endYear)...Jun(endYear)
        $sequence = $activeYear->getMonthSequence();

        // Bulan saat ini dalam sequence (batasi sampai bulan & tahun sekarang)
        $now = Carbon::now();
        $validSequence = array_filter($sequence, function ($s) use ($now) {
            $seqDate = Carbon::create($s['year'], $s['month'], 1);
            return $seqDate->lte($now->copy()->startOfMonth());
        });

        // Pre-load semua paid details (tanpa batasan year) untuk tahun ajaran ini
        $allPaidDetails = TransactionDetail::whereHas('transaction', fn($q) =>
            $q->where('academic_year_id', $activeYear->id)
        )
            ->with('transaction:id,student_id')
            ->get(['month', 'year', 'transaction_id'])
            ->groupBy(fn($d) => $d->transaction->student_id);

        $students = Student::with(['schoolClass'])
            ->where('academic_year_id', $activeYear->id)
            ->where('status', 'active')
            ->where('is_free_spp', false)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->orderBy('full_name')
            ->get()
            ->map(function ($student) use ($validSequence, $allPaidDetails) {
                $paidDetails = $allPaidDetails->get($student->id, collect());

                $arrearMonths = [];
                foreach ($validSequence as $s) {
                    $alreadyPaid = $paidDetails->contains(fn($p) =>
                        $p->month == $s['month'] && $p->year == $s['year']
                    );
                    if (!$alreadyPaid) {
                        $arrearMonths[] = $s; // simpan ['month'=>X, 'year'=>Y]
                    }
                }

                $student->arrear_months = $arrearMonths;
                $student->arrear_count  = count($arrearMonths);
                return $student;
            })
            ->filter(fn($s) => $s->arrear_count > 0)
            ->sortByDesc('arrear_count');

        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        return view('admin.reports.arrears', compact('students', 'activeYear', 'classes', 'monthNames'));
    }

    public function controlFile(Request $request)
    {
        $activeYear    = AcademicYear::active();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $selectedYearId = $request->academic_year_id ?? $activeYear?->id;

        // FIX #3: Gunakan objek tahun ajaran yang DIPILIH, bukan selalu tahun aktif
        $selectedYear = $selectedYearId
            ? AcademicYear::find($selectedYearId)
            : $activeYear;

        $students = Student::with(['schoolClass', 'transactions' => function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId)->with('details');
            }])
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->search, fn($q) => $q->where(fn($q2) => $q2->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('nisn', 'like', "%{$request->search}%")
                ->orWhere('parent_name', 'like', "%{$request->search}%")))
            ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
            ->orderBy('full_name')
            ->get();

        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('sort_order')->get();

        // FIX #3: Sequence dari tahun yang dipilih, bukan $activeYear
        $sequence = $selectedYear ? $selectedYear->getMonthSequence() : [
            ['month' => 7, 'year' => date('Y')], ['month' => 8, 'year' => date('Y')],
            ['month' => 9, 'year' => date('Y')], ['month' => 10, 'year' => date('Y')],
            ['month' => 11, 'year' => date('Y')], ['month' => 12, 'year' => date('Y')],
            ['month' => 1, 'year' => date('Y')+1], ['month' => 2, 'year' => date('Y')+1],
            ['month' => 3, 'year' => date('Y')+1], ['month' => 4, 'year' => date('Y')+1],
            ['month' => 5, 'year' => date('Y')+1], ['month' => 6, 'year' => date('Y')+1],
        ];

        $summary = [];
        foreach ($sequence as $s) {
            $m = $s['month'];
            $summary[$m] = [
                'belum_bayar'       => 0,
                'khusus'            => 0,
                'kolom_kosong'      => 0,
                'sudah_bayar'       => 0,
                'harus_bayar'       => 0,
                'bayar_sebelumnya'  => 0,
                'bayar_bersangkutan'=> 0,
            ];
        }

        foreach ($students as $student) {
            $paidData = [];
            foreach ($student->transactions as $tx) {
                foreach ($tx->details as $d) {
                    $paidData[$d->month] = [
                        'payment_date' => \Carbon\Carbon::parse($tx->payment_date),
                        'year'         => $d->year,
                    ];
                }
            }

            foreach ($sequence as $s) {
                $m = $s['month'];
                $y = $s['year'];

                if ($student->status !== 'active') {
                    $summary[$m]['kolom_kosong']++;
                } elseif ($student->is_free_spp) {
                    $summary[$m]['khusus']++;
                } else {
                    $summary[$m]['harus_bayar']++;
                    if (isset($paidData[$m]) && $paidData[$m]['year'] == $y) {
                        $summary[$m]['sudah_bayar']++;

                        $paymentDate  = $paidData[$m]['payment_date'];
                        $sppStartDate = \Carbon\Carbon::create($y, $m, 1)->startOfMonth();

                        if ($paymentDate->lt($sppStartDate)) {
                            $summary[$m]['bayar_sebelumnya']++;
                        } else {
                            $summary[$m]['bayar_bersangkutan']++;
                        }
                    } else {
                        $summary[$m]['belum_bayar']++;
                    }
                }
            }
        }

        return view('admin.reports.control-file', compact(
            'students', 'classes', 'academicYears', 'activeYear', 'selectedYearId', 'sequence', 'summary'
        ));
    }

    public function export(string $type, Request $request)
    {
        // TODO: Implement actual Excel export per $type (daily, monthly, yearly, etc.)
        return back()->with('error', "Export laporan '{$type}' belum tersedia.");
    }
}
