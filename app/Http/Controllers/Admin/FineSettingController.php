<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FineSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FineSettingController extends Controller
{
    public function index()
    {
        $settings = FineSetting::with('academicYear')->orderByDesc('academic_year_id')->get();
        return view('admin.fine-settings.index', compact('settings'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        return view('admin.fine-settings.form', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'due_date'         => 'required|integer|min:1|max:28',
            'fine_type'        => 'required|in:flat,daily',
            'amount'           => 'required|numeric|min:0',
        ]);

        FineSetting::updateOrCreate(
            ['academic_year_id' => $request->academic_year_id],
            $request->only('due_date', 'fine_type', 'amount', 'description')
        );

        // Invalidate cache fine setting
        Cache::forget("fine_setting_{$request->academic_year_id}");

        return redirect()->route('admin.fine-settings.index')
            ->with('success', 'Setting denda berhasil disimpan.');
    }

    public function edit(FineSetting $fineSetting)
    {
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        return view('admin.fine-settings.form', compact('fineSetting', 'academicYears'));
    }

    public function update(Request $request, FineSetting $fineSetting)
    {
        $request->validate([
            'due_date'  => 'required|integer|min:1|max:28',
            'fine_type' => 'required|in:flat,daily',
            'amount'    => 'required|numeric|min:0',
        ]);

        $fineSetting->update($request->only('due_date', 'fine_type', 'amount', 'description'));

        // Invalidate cache fine setting
        Cache::forget("fine_setting_{$fineSetting->academic_year_id}");

        return redirect()->route('admin.fine-settings.index')
            ->with('success', 'Setting denda berhasil diperbarui.');
    }

    public function destroy(FineSetting $fineSetting)
    {
        Cache::forget("fine_setting_{$fineSetting->academic_year_id}");
        $fineSetting->delete();
        return redirect()->route('admin.fine-settings.index')
            ->with('success', 'Setting denda berhasil dihapus.');
    }
}
