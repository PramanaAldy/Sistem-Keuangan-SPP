<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsTemplateDataSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Data Siswa';
    }

    public function headings(): array
    {
        return [
            'nisn',
            'nama_lengkap',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'kelas',
            'agama',
            'nama_orang_tua',
            'whatsapp',
            'alamat',
        ];
    }

    public function array(): array
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->pluck('display_name');
        $kelasCth1 = $classes->get(0, 'Kelas 1A');
        $kelasCth2 = $classes->get(1, 'Kelas 2B');
        $kelasCth3 = $classes->get(2, 'Kelas 3A');

        return [
            ['001', 'Ahmad Rizky Pratama', 'Jakarta',  '15/05/2017', 'Laki-laki', $kelasCth1, 'Islam', 'Budi Pratama',   '628111222333', 'Jl. Sudirman No 1'],
            ['002', 'Siti Nurhaliza',       'Bandung',  '20/08/2018', 'Perempuan', $kelasCth2, 'Islam', 'Ani Rahayu',     '628222333444', 'Jl. Thamrin No 2'],
            ['003', 'Budi Santoso',         'Surabaya', '10/03/2019', 'Laki-laki', $kelasCth3, 'Kristen', 'Hendra Santoso', '628333444555', 'Jl. Merdeka No 3'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        $sheet->getStyle('A2:J4')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}
