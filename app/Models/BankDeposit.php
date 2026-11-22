<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_date',
        'total_amount',
        'bank_1_percentage',
        'bank_1_amount',
        'bank_2_percentage',
        'bank_2_amount',
        'admin_id',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'total_amount' => 'decimal:2',
        'bank_1_percentage' => 'decimal:2',
        'bank_1_amount' => 'decimal:2',
        'bank_2_percentage' => 'decimal:2',
        'bank_2_amount' => 'decimal:2',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
