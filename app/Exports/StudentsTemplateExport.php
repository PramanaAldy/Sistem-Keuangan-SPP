<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new StudentsTemplateDataSheet(),
            new StudentsTemplatePetunjukSheet(),
        ];
    }
}
