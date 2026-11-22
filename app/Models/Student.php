<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'nisn',
        'religion',
        'full_name',
        'birth_date',
        'birth_place',
        'gender',
        'class_id',
        'academic_year_id',
        'status',
        'is_free_spp',
        'is_free_fine',
        'parent_name',
        'whatsapp',
        'address',
        'photo',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
        'is_free_spp' => 'boolean',
        'is_free_fine' => 'boolean',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'student_discounts')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function activeDiscounts(int $academicYearId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->discounts()->wherePivot('academic_year_id', $academicYearId)->get();
    }

    public function siblings(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_siblings', 'student_id', 'sibling_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Get paid months for a specific year
    public function paidMonths(int $year): array
    {
        return TransactionDetail::whereHas('transaction', fn($q) => $q->where('student_id', $this->id))
            ->where('year', $year)
            ->pluck('month')
            ->toArray();
    }
}
