<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Discount;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use App\Exports\StudentsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::active();
        $query = Student::with(['schoolClass', 'academicYear'])
            ->when($request->search, fn($q) => $q->where(fn($q2) => $q2->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('nisn', 'like', "%{$request->search}%")
                ->orWhere('parent_name', 'like', "%{$request->search}%")))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->academic_year_id, fn($q) => $q->where('academic_year_id', $request->academic_year_id),
                fn($q) => $activeYear ? $q->where('academic_year_id', $activeYear->id) : $q)
            ->orderBy('full_name');

        $students = $query->paginate(20)->withQueryString();
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        return view('admin.students.index', compact('students', 'classes', 'academicYears', 'activeYear'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $activeYear = AcademicYear::active();
        return view('admin.students.form', compact('classes', 'academicYears', 'activeYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'nullable|string|unique:students,nisn',
            'full_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:active,inactive,alumni,cuti',
            'gender' => 'nullable|in:L,P',
            'religion' => 'nullable|string|max:50',
        ]);

        Student::create($request->only(
            'nisn', 'full_name', 'birth_date', 'birth_place', 'gender', 'religion',
            'class_id', 'academic_year_id', 'status', 'is_free_spp',
            'is_free_fine', 'parent_name', 'whatsapp', 'address'
        ) + [
            'is_free_spp' => $request->boolean('is_free_spp'),
            'is_free_fine' => $request->boolean('is_free_fine'),
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass', 'academicYear', 'transactions.details', 'siblings', 'discounts']);
        $activeYear = AcademicYear::active();
        $discounts = Discount::where('is_active', true)->get();
        $availableSiblings = Student::where('id', '!=', $student->id)
            ->where('academic_year_id', $student->academic_year_id)
            ->orderBy('full_name')->get();

        return view('admin.students.show', compact('student', 'activeYear', 'discounts', 'availableSiblings'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        return view('admin.students.form', compact('student', 'classes', 'academicYears'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nisn' => "nullable|string|unique:students,nisn,{$student->id}",
            'full_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:active,inactive,alumni,cuti',
        ]);

        $student->update($request->only(
            'nisn', 'full_name', 'birth_date', 'birth_place', 'gender', 'religion',
            'class_id', 'academic_year_id', 'status',
            'parent_name', 'whatsapp', 'address'
        ) + [
            'is_free_spp' => $request->boolean('is_free_spp'),
            'is_free_fine' => $request->boolean('is_free_fine'),
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $this->performDelete($student);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa dan seluruh data terkait berhasil dihapus.');
    }

    private function performDelete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            // 1. Hapus detail transaksi dahulu (child dari transaction)
            $transactionIds = $student->transactions()->pluck('id');
            if ($transactionIds->isNotEmpty()) {
                \App\Models\TransactionDetail::whereIn('transaction_id', $transactionIds)->delete();
            }

            // 2. Hapus transaksi
            $student->transactions()->delete();

            // 3. Hapus relasi saudara (pivot table bidirectional)
            DB::table('student_siblings')
                ->where('student_id', $student->id)
                ->orWhere('sibling_id', $student->id)
                ->delete();

            // 4. Hapus relasi diskon
            DB::table('student_discounts')
                ->where('student_id', $student->id)
                ->delete();

            // 5. Hapus siswa
            $student->delete();
        });
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,alumni,promote_class,update_year,transfer_class,delete',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'target_class_id' => 'nullable|required_if:action,transfer_class|exists:classes,id',
        ]);

        $students = Student::whereIn('id', $request->student_ids)->get();
        $activeYear = AcademicYear::active();

        DB::transaction(function () use ($students, $request, $activeYear) {
            /** @var \App\Models\Student $student */
            foreach ($students as $student) {
                match ($request->action) {
                    'activate' => $student->update(['status' => 'active']),
                    'deactivate' => $student->update(['status' => 'inactive']),
                    'alumni' => $student->update(['status' => 'alumni']),
                    'promote_class' => $this->handlePromotion($student, true, $activeYear),
                    'update_year' => $this->handlePromotion($student, false, $activeYear),
                    'transfer_class' => $student->update(['class_id' => $request->target_class_id]),
                    'delete' => $this->performDelete($student),
                };
            }
        });

        $count = count($request->student_ids);
        return back()->with('success', "$count siswa berhasil diproses.");
    }

    private function handlePromotion(Student $student, bool $promoteClass, ?AcademicYear $activeYear): void
    {
        $updateData = [];

        if ($promoteClass) {
            $currentClass = $student->schoolClass;
            if ($currentClass) {
                $name = trim($currentClass->name);
                $nextName = null;

                // Cek apakah format kelas mengandung angka (mis. "1A", "4B", "7")
                if (preg_match('/^(\d+)(.*)$/', $name, $matches)) {
                    $grade = (int)$matches[1];
                    $suffix = trim($matches[2]);
                    
                    $nextGrade = $grade + 1;
                    
                    // Khusus SD kelas 6 (6A, 6B, dsb) akan otomatis lulus ke jenjang "7" SMP yang tidak punya suffix huruf
                    if ($nextGrade == 7) {
                        $nextName = "7";
                    } else {
                        // 1A jadi 2A, 2B jadi 3B, 7 jadi 8
                        $nextName = $nextGrade . $suffix;
                    }
                } else {
                    // Penanganan kelas paud/TK yang pakai string non-angka
                    $nameUpper = strtoupper($name);
                    if ($nameUpper === 'PG') $nextName = 'TK A';
                    elseif ($nameUpper === 'TK A') $nextName = 'TK B';
                    elseif ($nameUpper === 'TK B') $nextName = '1A';
                }

                if ($nextName) {
                    // Cari ID kelas tujuan dari database sesuai tebakan nama di atas
                    $nextClass = SchoolClass::where('name', $nextName)->first();

                    if ($nextClass) {
                        $updateData['class_id'] = $nextClass->id;
                    }
                }
            }
        }

        if ($activeYear && $student->academic_year_id !== $activeYear->id) {
            $updateData['academic_year_id'] = $activeYear->id;
        }

        if (!empty($updateData)) {
            $student->update($updateData);
        }
    }

    public function setSibling(Request $request, Student $student)
    {
        $request->validate(['sibling_id' => 'required|exists:students,id']);

        if ((int) $request->sibling_id === $student->id) {
            return back()->with('error', 'Siswa tidak dapat dijadikan saudara dari dirinya sendiri.');
        }

        $sibling = Student::findOrFail($request->sibling_id);

        DB::transaction(function () use ($student, $sibling) {
            // Bidirectional relationship
            DB::table('student_siblings')->insertOrIgnore([
                ['student_id' => $student->id, 'sibling_id' => $sibling->id, 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $sibling->id, 'sibling_id' => $student->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        });

        return back()->with('success', 'Saudara berhasil ditambahkan.');
    }

    public function removeSibling(Student $student, Student $sibling)
    {
        DB::table('student_siblings')
            ->where(fn($q) => $q->where('student_id', $student->id)->where('sibling_id', $sibling->id))
            ->orWhere(fn($q) => $q->where('student_id', $sibling->id)->where('sibling_id', $student->id))
            ->delete();

        return back()->with('success', 'Saudara berhasil dihapus.');
    }

    public function setDiscount(Request $request, Student $student)
    {
        $request->validate([
            'discount_id' => 'required|exists:discounts,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        DB::table('student_discounts')->insertOrIgnore([
            'student_id' => $student->id,
            'discount_id' => $request->discount_id,
            'academic_year_id' => $request->academic_year_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Diskon berhasil ditambahkan ke siswa.');
    }

    public function removeDiscount(Student $student, Discount $discount)
    {
        DB::table('student_discounts')
            ->where('student_id', $student->id)
            ->where('discount_id', $discount->id)
            ->delete();

        return back()->with('success', 'Diskon berhasil dihapus dari siswa.');
    }

    public function export()
    {
        return Excel::download(new StudentsExport, 'data-siswa.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport, 'template-import-siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new StudentsImport, $request->file('file'));
        return back()->with('success', 'Data siswa berhasil diimport.');
    }
}
