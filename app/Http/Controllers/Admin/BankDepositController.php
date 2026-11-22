<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankDepositController extends Controller
{
    public function index(Request $request)
    {
        $query = BankDeposit::with('admin')->orderByDesc('deposit_date')->orderByDesc('id');

        if ($request->month && $request->year) {
            $query->whereMonth('deposit_date', $request->month)
                  ->whereYear('deposit_date', $request->year);
        }

        $deposits = $query->paginate(15)->withQueryString();

        return view('admin.bank-deposits.index', compact('deposits'));
    }

    public function create(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        
        // Hitung pemasukan Kasir (Tunai) otomatis pada tanggal yang dipilih
        // Pembayaran yang kurang atau null pada sistem lama menggunakan grand_total
        $totalCash = Transaction::whereDate('payment_date', $date)
            ->where('spp_method', 'cash')
            ->get()
            ->sum(fn($t) => $t->paid_amount ?: $t->grand_total);

        return view('admin.bank-deposits.create', compact('date', 'totalCash'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deposit_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'bank_1_percentage' => 'required|numeric|min:0|max:100',
            'bank_1_amount' => 'required|numeric|min:0',
            'bank_2_percentage' => 'required|numeric|min:0|max:100',
            'bank_2_amount' => 'required|numeric|min:0',
        ]);

        if (round((float)$request->bank_1_percentage + (float)$request->bank_2_percentage, 2) != 100) {
            return back()->withInput()->with('error', 'Total Persentase harus 100%');
        }

        if (round((float)$request->bank_1_amount + (float)$request->bank_2_amount, 2) != round((float)$request->total_amount, 2)) {
            return back()->withInput()->with('error', 'Total jumlah pembagian harus sama dengan Total Nominal.');
        }

        BankDeposit::create([
            'deposit_date' => $request->deposit_date,
            'total_amount' => $request->total_amount,
            'bank_1_percentage' => $request->bank_1_percentage,
            'bank_1_amount' => $request->bank_1_amount,
            'bank_2_percentage' => $request->bank_2_percentage,
            'bank_2_amount' => $request->bank_2_amount,
            'admin_id' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.bank-deposits.index')->with('success', 'Setoran bank berhasil dicatat.');
    }

    public function destroy(BankDeposit $bankDeposit)
    {
        $bankDeposit->delete();
        return redirect()->route('admin.bank-deposits.index')->with('success', 'Riwayat setoran berhasil dihapus.');
    }

    public function print(Request $request)
    {
        $query = BankDeposit::with('admin')->orderByDesc('deposit_date')->orderByDesc('id');

        if ($request->month && $request->year) {
            $query->whereMonth('deposit_date', $request->month)
                  ->whereYear('deposit_date', $request->year);
        }

        $deposits = $query->get();

        return view('admin.bank-deposits.print', compact('deposits'));
    }
}
