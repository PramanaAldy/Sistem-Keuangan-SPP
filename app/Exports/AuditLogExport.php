<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $event = null,
        protected ?string $admin = null,
        protected ?string $date  = null,
    ) {}

    public function query()
    {
        $q = AuditLog::query()->latest();
        if ($this->event) $q->where('event', $this->event);
        if ($this->admin) $q->where('admin_name', 'like', '%' . $this->admin . '%');
        if ($this->date)  $q->whereDate('created_at', $this->date);
        return $q;
    }

    public function title(): string
    {
        return 'Audit Log';
    }

    public function headings(): array
    {
        return ['#', 'Waktu', 'Event', 'Admin', 'Deskripsi', 'Target', 'ID Target', 'IP Address'];
    }

    public function map($log): array
    {
        $eventLabel = match($log->event) {
            'created'  => 'Dibuat',
            'updated'  => 'Diubah',
            'deleted'  => 'Dihapus',
            'login'    => 'Login',
            'logout'   => 'Logout',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($log->event),
        };

        $target = $log->auditable_type
            ? collect(explode('\\', $log->auditable_type))->last()
            : '-';

        return [
            $log->id,
            $log->created_at->format('d/m/Y H:i:s'),
            $eventLabel,
            $log->admin_name ?? '-',
            $log->description,
            $target,
            $log->auditable_id ?? '-',
            $log->ip_address,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
