<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsDataSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): ?Student
    {
        $kelasStr = trim($row['kelas']);
        $class = SchoolClass::where('display_name', $kelasStr)->orWhere('name', $kelasStr)->first();
        $year = AcademicYear::where('is_active', true)->first();

        if (!$class || !$year) return null;

        $nisn = null;
        if (isset($row['nisn'])) {
            $nisn = trim($row['nisn']);
        } elseif (isset($row['nis'])) {
            $nisn = trim($row['nis']);
        }
        
        $student = null;

        if ($nisn) {
            $student = Student::where('nisn', $nisn)->first();
        } else {
            $student = Student::where('full_name', trim($row['nama_lengkap']))
                              ->where('class_id', $class->id)
                              ->first();
        }

        $genderStr = strtolower(trim($row['jenis_kelamin'] ?? ''));
        $gender = (str_contains($genderStr, 'laki') || $genderStr === 'l') ? 'L' : 'P';

        $attributes = [
            'nisn' => $nisn,
            'full_name' => trim($row['nama_lengkap']),
            'birth_date' => $this->parseDate($row['tanggal_lahir']),
            'birth_place' => isset($row['tempat_lahir']) ? trim($row['tempat_lahir']) : null,
            'gender' => $gender,
            'religion' => isset($row['agama']) ? trim($row['agama']) : null,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
            'parent_name' => isset($row['nama_orang_tua']) ? trim($row['nama_orang_tua']) : null,
            'whatsapp' => isset($row['whatsapp']) ? trim($row['whatsapp']) : null,
            'address' => isset($row['alamat']) ? trim($row['alamat']) : null,
        ];

        if ($student) {
            $student->fill($attributes);
            return $student;
        }

        return new Student($attributes);
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;

        if (is_numeric($date)) {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        }
        
        $date = trim($date);
        
        // Handle common typos: "23-10-2018", "23-102018", "23/102018"
        $date = str_replace('-', '/', $date);
        if (preg_match('/^(\d{1,2})\/(\d{1,2})(\d{4})$/', $date, $matches)) {
            $date = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }

        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::parse($date);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    public function rules(): array
    {
        return [
            'nisn' => 'nullable',
            'nis' => 'nullable',
            'nama_lengkap' => 'required|string',
            'tanggal_lahir' => [
                'required',
                function ($attribute, $value, $onFailure) {
                    if (empty($value)) return;
                    if (is_numeric($value)) return;

                    $date = trim($value);
                    $date = str_replace('-', '/', $date);
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})(\d{4})$/', $date, $matches)) {
                        $date = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
                    }

                    try {
                        \Carbon\Carbon::createFromFormat('d/m/Y', $date);
                    } catch (\Exception $e) {
                        try {
                            \Carbon\Carbon::parse($date);
                        } catch (\Exception $e) {
                            $onFailure('Format tanggal lahir "' . $value . '" tidak valid. Gunakan format DD/MM/YYYY.');
                        }
                    }
                }
            ],
            'kelas' => [
                'required',
                'string',
                function ($attribute, $value, $onFailure) {
                    $kelasStr = trim($value);
                    $exists = SchoolClass::where('display_name', $kelasStr)->orWhere('name', $kelasStr)->exists();
                    if (!$exists) {
                        $onFailure('Kelas "' . $value . '" tidak ditemukan. Pastikan nama kelas persis seperti "Kelas 1A" atau "1A".');
                    }
                }
            ],
        ];
    }
}
