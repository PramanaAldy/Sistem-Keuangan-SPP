<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'receipt_number', 'student_id', 'academic_year_id', 'admin_id',
        'payment_date', 'spp_amount', 'fine_amount', 'discount_amount',
        'grand_total', 'spp_method', 'fine_method', 'notes',
        'paid_amount', 'proof_image', 'transfer_nominal'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'spp_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'transfer_nominal' => 'decimal:2',
    ];

    public function getSelisihAttribute(): float
    {
        return max(0, $this->paid_amount - $this->grand_total);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'SPP-' . date('Ymd');
        $last = self::where('receipt_number', 'like', $prefix . '%')
            ->orderByDesc('id')->first();
        $num = $last ? (int) substr($last->receipt_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
