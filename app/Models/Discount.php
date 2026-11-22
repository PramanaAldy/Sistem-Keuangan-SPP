<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    protected $fillable = ['name', 'type', 'value', 'is_percent', 'description', 'is_active'];

    protected $casts = [
        'is_percent' => 'boolean',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_discounts')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->is_percent) {
            return $this->value . '%';
        }
        return 'Rp ' . number_format((float)$this->value, 0, ',', '.');
    }

    public function calculateDiscount(float $sppAmount): float
    {
        if ($this->is_percent) {
            return $sppAmount * ($this->value / 100);
        }
        return min($this->value, $sppAmount);
    }
}
