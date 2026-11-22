<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentConfirmation extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'proof_image',
        'note',
        'months_json',
        'total_amount',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'months_json' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /** Months as human-readable list, e.g. "Januari 2025, Februari 2025" */
    public function getMonthsListAttribute(): string
    {
        if (!$this->months_json || !is_iterable($this->months_json)) {
            return '-';
        }

        $names = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return collect($this->months_json)
            ->filter(fn($m) => is_array($m) && isset($m['month']) && isset($m['year']))
            ->map(fn($m) => ($names[$m['month']] ?? $m['month']) . ' ' . $m['year'])
            ->join(', ') ?: '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'var(--accent)',
            'approved' => 'var(--green-light)',
            'rejected' => 'var(--red-light)',
            default => 'var(--text-muted)',
        };
    }
}
