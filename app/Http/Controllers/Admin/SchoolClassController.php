<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    private array $levels = ['PG', 'TK', 'SD', 'SMP'];

    public function index()
    {
        $classes = SchoolClass::orderBy('sort_order')->orderBy('level')->orderBy('name')->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $levels = $this->levels;
        return view('admin.classes.form', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'level' => 'required|in:PG,TK,SD,SMP',
            'name' => 'required|string|max:20',
            'display_name' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        SchoolClass::create($request->only('level', 'name', 'display_name', 'sort_order'));
        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        $levels = $this->levels;
        return view('admin.classes.form', compact('class', 'levels'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'level' => 'required|in:PG,TK,SD,SMP',
            'name' => 'required|string|max:20',
            'display_name' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $class->update([
            ...$request->only('level', 'name', 'display_name', 'sort_order'),
            'is_active' => $request->boolean('is_active'),
        ]);
        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->students()->count() > 0) {
            return back()->with('error', 'Kelas ini masih memiliki siswa terdaftar.');
        }
        $class->delete();
        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
