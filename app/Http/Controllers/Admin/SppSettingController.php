<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\SppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SppSettingController extends Controller
{
    public function index()
    {
        $settings = SppSetting::with(['schoolClass', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderByRaw('(SELECT sort_order FROM classes WHERE id = class_id)')
            ->get();
        $activeYear = AcademicYear::active();
        return view('admin.spp-settings.index', compact('settings', 'activeYear'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $activeYear = AcademicYear::active();
        return view('admin.spp-settings.form', compact('classes', 'academicYears', 'activeYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'amount'           => 'required|numeric|min:0',
        ]);

        SppSetting::updateOrCreate(
            ['class_id' => $request->class_id, 'academic_year_id' => $request->academic_year_id],
            ['amount' => $request->amount, 'description' => $request->description]
        );

        // Invalidate cache tarif SPP untuk kelas+tahun ini
        Cache::forget("spp_setting_{$request->class_id}_{$request->academic_year_id}");

        return redirect()->route('admin.spp-settings.index')
            ->with('success', 'Nominal SPP berhasil disimpan.');
    }

    public function edit(SppSetting $sppSetting)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        return view('admin.spp-settings.form', compact('sppSetting', 'classes', 'academicYears'));
    }

    public function update(Request $request, SppSetting $sppSetting)
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $sppSetting->update(['amount' => $request->amount, 'description' => $request->description]);

        // Invalidate cache saat tarif diubah
        Cache::forget("spp_setting_{$sppSetting->class_id}_{$sppSetting->academic_year_id}");

        return redirect()->route('admin.spp-settings.index')
            ->with('success', 'Nominal SPP berhasil diperbarui.');
    }

    public function destroy(SppSetting $sppSetting)
    {
        Cache::forget("spp_setting_{$sppSetting->class_id}_{$sppSetting->academic_year_id}");
        $sppSetting->delete();
        return redirect()->route('admin.spp-settings.index')
            ->with('success', 'Setting SPP berhasil dihapus.');
    }
}
