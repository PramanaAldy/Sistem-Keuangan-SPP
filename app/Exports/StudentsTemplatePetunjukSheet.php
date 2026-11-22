<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsTemplatePetunjukSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        $classes   = SchoolClass::where('is_active', true)->orderBy('sort_order')->pluck('display_name')->toArray();
        $kelasList = implode(', ', $classes) ?: 'Lihat data kelas di sistem';

        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT SISWA'],
            [''],
            ['KOLOM',          'KETERANGAN',                                                  'CONTOH',        'WAJIB?'],
            ['nisn',           'Nomor Induk Siswa Nasional (boleh kosong)',                   '001',           'Tidak'],
            ['nama_lengkap',   'Nama lengkap siswa',                                          'Ahmad Rizky',   'Ya'],
            ['tempat_lahir',   'Kota tempat lahir siswa',                                     'Jakarta',       'Tidak'],
            ['tanggal_lahir',  'Format: DD/MM/YYYY',                                          '15/05/2017',    'Ya'],
            ['jenis_kelamin',  'Isi dengan: Laki-laki  atau  Perempuan',                      'Laki-laki',     'Tidak'],
            ['kelas',          'Nama kelas PERSIS seperti di sistem (case-sensitive)',         $kelasList,      'Ya'],
            ['agama',          'Isi dengan: Islam, Kristen, Katolik, Hindu, Buddha, dsb',     'Islam',         'Tidak'],
            ['nama_orang_tua', 'Nama lengkap orang tua / wali',                               'Budi Pratama',  'Tidak'],
            ['whatsapp',       'Nomor WA dengan kode negara tanpa + (misal 628xxx)',           '628111222333',  'Tidak'],
            ['alamat',         'Alamat tempat tinggal siswa',                                  'Jl. Sudirman No 1', 'Tidak'],
            [''],
            ['CATATAN PENTING:'],
            ['1. Jangan ubah nama kolom pada baris pertama sheet "Data Siswa" (huruf kecil, gunakan underscore).'],
            ['2. Kolom "kelas" harus persis sama dengan nama kelas di sistem. Daftar kelas yang tersedia:'],
            ['   ' . $kelasList],
            ['3. Tanggal lahir harus dalam format DD/MM/YYYY (contoh: 15/05/2017).'],
            ['4. Jenis kelamin harus: Laki-laki atau Perempuan (huruf kapital pada awal kata).'],
            ['5. Nomor WhatsApp gunakan format internasional: 628xxx (tanpa + atau 0 di depan).'],
            ['6. Hapus baris contoh (baris 2, 3, 4) sebelum mengisi data asli, atau timpa langsung.'],
            ['7. Simpan file dalam format .xlsx atau .xls sebelum diimport.'],
            ['8. Siswa yang diimport akan otomatis masuk ke tahun ajaran aktif dengan status Aktif.'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
        ]);

        $sheet->getStyle('A3:D3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D6A4F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        $sheet->getStyle('A4:D14')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getStyle('A15')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'C0392B']],
        ]);

        foreach ([4, 6, 8, 10, 12] as $row) {
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8F0']],
            ]);
        }

        return [];
    }
}
