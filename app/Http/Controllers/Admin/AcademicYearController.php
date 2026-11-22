<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderByDesc('start_year')->get();
        return view('admin.academic-years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic-years.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'start_year' => 'required|integer|min:2000|max:2099',
            'end_year' => 'required|integer|min:2000|max:2099|gte:start_year',
        ]);

        AcademicYear::create($request->only('name', 'start_year', 'end_year'));
        \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.form', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'start_year' => 'required|integer|min:2000|max:2099',
            'end_year' => 'required|integer|min:2000|max:2099|gte:start_year',
        ]);

        $academicYear->update($request->only('name', 'start_year', 'end_year'));
        
        if ($academicYear->is_active) {
            \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        }
        
        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran aktif tidak dapat dihapus.');
        }
        $academicYear->delete();
        \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function setActive($id)
    {
        AcademicYear::query()->update(['is_active' => false]);
        AcademicYear::findOrFail($id)->update(['is_active' => true]);
        
        \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        
        return back()->with('success', 'Tahun ajaran aktif berhasil diubah.');
    }
}
