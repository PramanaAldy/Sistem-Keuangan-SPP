<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FineSetting extends Model
{
    protected $fillable = ['academic_year_id', 'due_date', 'fine_type', 'amount', 'description'];

    protected $casts = ['amount' => 'decimal:2'];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Calculate fine for a payment made on $paymentDate for a given $month/$year.
     */
    public function calculateFine(Carbon $paymentDate, int $month, int $year): float
    {
        $dueDate = Carbon::create($year, $month, $this->due_date);

        if ($paymentDate->lte($dueDate)) {
            return 0.0;
        }

        if ($this->fine_type === 'daily') {
            $lateDays = $dueDate->diffInDays($paymentDate);
            return (float)($lateDays * $this->amount);
        }

        return (float)$this->amount; // flat
    }
}
