<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Student::with(['schoolClass', 'academicYear'])
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NISN', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir',
            'Jenis Kelamin', 'Agama', 'Kelas', 'Tahun Ajaran', 'Status',
            'Nama Orang Tua', 'WhatsApp', 'Alamat',
        ];
    }

    public function map($student): array
    {
        return [
            $student->nisn,
            $student->full_name,
            $student->birth_place,
            $student->birth_date?->format('d/m/Y'),
            $student->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $student->religion,
            $student->schoolClass?->display_name,
            $student->academicYear?->name,
            $student->status,
            $student->parent_name,
            $student->whatsapp,
            $student->address,
        ];
    }
}
