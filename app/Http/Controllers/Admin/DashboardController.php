<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $activeYear = AcademicYear::active();
        $admin = Auth::guard('admin')->user();

        $data = $this->getDashboardData($activeYear);

        return view('admin.dashboard.index', array_merge($data, [
            'activeYear' => $activeYear,
            'admin'      => $admin,
        ]));
    }

    /**
     * AJAX endpoint: kembalikan stats terbaru sebagai JSON (untuk auto-refresh).
     */
    public function stats()
    {
        $activeYear = AcademicYear::active();
        $data = $this->getDashboardData($activeYear);

        // Serialisasi recentTransactions agar bisa jadi JSON
        $recentTx = $data['recentTransactions']->map(function ($tx) {
            $months = $tx->details->map(fn($d) => $d->month)->toArray();
            return [
                'receipt_number' => $tx->receipt_number,
                'student_name'   => $tx->student->full_name,
                'class_name'     => $tx->student->schoolClass?->display_name,
                'payment_date'   => $tx->payment_date->format('d M Y'),
                'months'         => $months,
                'grand_total'    => $tx->grand_total,
                'spp_method'     => strtoupper($tx->spp_method),
            ];
        });

        return response()->json([
            'totalIncomeThisMonth' => $data['totalIncomeThisMonth'],
            'totalStudents'        => $data['totalStudents'],
            'totalArrearsCount'    => $data['totalArrearsCount'],
            'totalCash'            => $data['totalCash'] ?? 0,
            'totalTransfer'        => $data['totalTransfer'] ?? 0,
            'totalDebit'           => $data['totalDebit'] ?? 0,
            'recentTransactions'   => $recentTx,
            'monthlyChart'         => $data['monthlyChart'],
        ]);
    }

    /**
     * Hitung semua data dashboard — di-cache per tahun ajaran aktif.
     */
    private function getDashboardData(?AcademicYear $activeYear): array
    {
        $totalStudents        = 0;
        $totalIncomeThisMonth = 0;
        $totalArrearsCount    = 0;
        $recentTransactions   = collect();
        $monthlyChart         = [];

        if (!$activeYear) {
            return compact(
                'totalStudents', 'totalIncomeThisMonth',
                'totalArrearsCount', 'recentTransactions', 'monthlyChart'
            );
        }

        $yearId    = $activeYear->id;
        $thisMonth = Carbon::now()->month;
        $thisYear  = Carbon::now()->year;

        // ── Stats card: cache 10 menit ─────────────────────────────────────
        $todayDate = Carbon::today()->format('Y_m_d');
        $stats = Cache::remember("dashboard_stats_v3_{$yearId}_{$todayDate}", 600, function () use ($yearId, $thisMonth, $thisYear) {
            $totalStudents = Student::where('academic_year_id', $yearId)
                ->where('status', 'active')->count();

            $totalIncomeThisMonth = Transaction::where('academic_year_id', $yearId)
                ->whereMonth('payment_date', $thisMonth)
                ->whereYear('payment_date', $thisYear)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(paid_amount, grand_total)'));

            // FIX #4: Cari bulan & tahun yang sesuai dalam urutan tahun ajaran
            // Bulan Januari-Juni termasuk tahun berikutnya dalam tahun ajaran
            $activeYearObj = AcademicYear::where('id', $yearId)->first();
            $sequence = $activeYearObj ? $activeYearObj->getMonthSequence() : [];
            $currentSeq = collect($sequence)->first(fn($s) => $s['month'] == $thisMonth) 
                ?? ['month' => $thisMonth, 'year' => $thisYear];
            $checkMonth = $currentSeq['month'];
            $checkYear  = $currentSeq['year'];

            $paidStudentIds = DB::table('transaction_details')
                ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transactions.academic_year_id', $yearId)
                ->where('transaction_details.month', $checkMonth)
                ->where('transaction_details.year', $checkYear)
                ->pluck('transactions.student_id')
                ->unique()->toArray();

            $arrearsStudentsQuery = Student::with('schoolClass')
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->where('is_free_spp', false)
                ->whereNotIn('id', $paidStudentIds);

            $totalArrearsCount = $arrearsStudentsQuery->count();
            $arrearsStudents = $arrearsStudentsQuery->get();

            $today = Carbon::today();
            $dailyTransactions = Transaction::where('academic_year_id', $yearId)
                ->whereDate('payment_date', $today)
                ->get();
            $totalCash = $dailyTransactions->where('spp_method', 'cash')->sum('grand_total');
            $totalTransfer = $dailyTransactions->where('spp_method', 'transfer')->sum('grand_total');
            $totalDebit = $dailyTransactions->where('spp_method', 'debit')->sum('grand_total');

            $freeStudents = Student::with('schoolClass')
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->where('is_free_spp', true)
                ->get();

            $khususStudents = Student::with('schoolClass')
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->where('is_free_spp', false)
                ->where(function ($q) use ($yearId) {
                    $q->whereHas('discounts', function ($q2) use ($yearId) {
                        $q2->where('student_discounts.academic_year_id', $yearId);
                    })->orWhereHas('siblings');
                })
                ->get();

            $bayarStudents = Student::with('schoolClass')
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->where('is_free_spp', false)
                ->whereDoesntHave('discounts', function ($q) use ($yearId) {
                    $q->where('student_discounts.academic_year_id', $yearId);
                })
                ->whereDoesntHave('siblings')
                ->get();

            return compact('totalStudents', 'totalIncomeThisMonth', 'totalArrearsCount', 'arrearsStudents', 'totalCash', 'totalTransfer', 'totalDebit', 'freeStudents', 'khususStudents', 'bayarStudents');
        });

        // FIX #18: Gunakan COALESCE(paid_amount, grand_total) agar konsisten dengan laporan lain
        $rawChart = Cache::remember("dashboard_chart_{$yearId}_{$thisYear}", 600, function () use ($yearId, $thisYear) {
            return DB::table('transactions')
                ->selectRaw('MONTH(payment_date) as month, SUM(COALESCE(paid_amount, grand_total)) as total')
                ->where('academic_year_id', $yearId)
                ->whereYear('payment_date', $thisYear)
                ->groupByRaw('MONTH(payment_date)')
                ->pluck('total', 'month');
        });

        for ($m = 1; $m <= 12; $m++) {
            $monthlyChart[] = [
                'month' => $m,
                'total' => (float) ($rawChart[$m] ?? 0),
            ];
        }

        // ── Transaksi terbaru: TIDAK di-cache (harus real-time) ─────────────
        $recentTransactions = Transaction::with(['student', 'student.schoolClass', 'details'])
            ->where('academic_year_id', $yearId)
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        return array_merge($stats, compact('recentTransactions', 'monthlyChart'));
    }
}
