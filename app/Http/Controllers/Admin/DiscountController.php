<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::orderBy('type')->orderBy('name')->get();
        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        $types = ['family' => 'Keluarga', 'achievement' => 'Berprestasi', 'poor' => 'Kurang Mampu', 'other' => 'Lainnya'];
        return view('admin.discounts.form', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:family,achievement,poor,other',
            'value' => 'required|numeric|min:0',
            'is_percent' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        Discount::create([
            ...$request->only('name', 'type', 'value', 'description'),
            'is_percent' => $request->boolean('is_percent'),
        ]);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function edit(Discount $discount)
    {
        $types = ['family' => 'Keluarga', 'achievement' => 'Berprestasi', 'poor' => 'Kurang Mampu', 'other' => 'Lainnya'];
        return view('admin.discounts.form', compact('discount', 'types'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:family,achievement,poor,other',
            'value' => 'required|numeric|min:0',
        ]);

        $discount->update([
            ...$request->only('name', 'type', 'value', 'description'),
            'is_percent' => $request->boolean('is_percent'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil dihapus.');
    }
}
