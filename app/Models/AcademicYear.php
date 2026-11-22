<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'start_year', 'end_year', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function sppSettings(): HasMany
    {
        return $this->hasMany(SppSetting::class);
    }

    public function fineSettings(): HasMany
    {
        return $this->hasMany(FineSetting::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Mendapatkan urutan kronologis bulan dalam tahun ajaran (Juli - Juni)
     * @return array Array of objects with 'month' and 'year'
     */
    public function getMonthSequence(): array
    {
        $sequence = [];
        
        // Juli sampai Desember (Tahun Mulai)
        for ($m = 7; $m <= 12; $m++) {
            $sequence[] = ['month' => $m, 'year' => $this->start_year];
        }
        
        // Januari sampai Juni (Tahun Selesai)
        for ($m = 1; $m <= 6; $m++) {
            $sequence[] = ['month' => $m, 'year' => $this->end_year];
        }
        
        return $sequence;
    }

    public static function active(): ?self
    {
        return \Illuminate\Support\Facades\Cache::remember('active_academic_year', 3600, function () {
            return self::where('is_active', true)->first();
        });
    }
}
