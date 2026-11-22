<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['level', 'name', 'display_name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function sppSettings(): HasMany
    {
        return $this->hasMany(SppSetting::class, 'class_id');
    }

    public function getSppAmountForYear(int $academicYearId): ?float
    {
        return $this->sppSettings()
            ->where('academic_year_id', $academicYearId)
            ->value('amount');
    }
}
