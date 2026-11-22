<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'event', 'auditable_type', 'auditable_id',
        'admin_id', 'admin_name', 'ip_address',
        'description', 'old_values', 'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Helper statis: catat activity dengan mudah.
     *
     * Contoh:
     *   AuditLog::record('created', $transaction, 'Transaksi baru dibuat');
     */
    public static function record(
        string $event,
        ?Model $target = null,
        string $description = '',
        array $oldValues = [],
        array $newValues = []
    ): void {
        $admin = Auth::guard('admin')->user();

        static::create([
            'event'           => $event,
            'auditable_type'  => $target ? get_class($target) : null,
            'auditable_id'    => $target?->getKey(),
            'admin_id'        => $admin?->id,
            'admin_name'      => $admin?->name ?? 'System',
            'ip_address'      => Request::ip(),
            'description'     => $description,
            'old_values'      => $oldValues ?: null,
            'new_values'      => $newValues ?: null,
        ]);
    }

    /** Label event yang mudah dibaca */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created'  => 'Dibuat',
            'updated'  => 'Diubah',
            'deleted'  => 'Dihapus',
            'login'    => 'Login',
            'logout'   => 'Logout',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->event),
        };
    }

    /** Warna badge untuk event */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created'  => 'badge-success',
            'updated'  => 'badge-warning',
            'deleted'  => 'badge-danger',
            'login'    => 'badge-info',
            'logout'   => 'badge-secondary',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default    => 'badge-secondary',
        };
    }

    /** Nama model pendek tanpa namespace */
    public function getTargetNameAttribute(): string
    {
        if (!$this->auditable_type) return '-';
        $parts = explode('\\', $this->auditable_type);
        return end($parts);
    }
}
