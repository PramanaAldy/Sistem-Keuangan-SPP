<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class StudentsImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        return [
            0 => new StudentsDataSheetImport(), // Only import the first sheet
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Ignore other sheets
    }
}
