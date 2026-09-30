<?php

namespace App\Exports;

use App\Models\DailyCase;
use App\Services\ReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CasesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters)
    {
    }

    public function collection(): Collection
    {
        return app(ReportService::class)
            ->filteredCaseQuery($this->filters)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'N° Caso', 'Fecha recepción', 'Hora', 'Fecha límite', 'Solicitante',
            'Usuario afectado', 'Cargo', 'Tipo solicitud', 'Aplicación', 'Perfil',
            'Permiso', 'Grupo', 'Familia', 'Prioridad', 'Estado', 'Analista',
            'Fecha inicio', 'Fecha fin', 'Concepto GSI', 'Resultado', 'Observaciones',
        ];
    }

    public function map($case): array
    {
        return [
            $case->internal_id,
            $case->case_number,
            $case->received_date?->format('d/m/Y'),
            $case->received_time?->format('H:i'),
            $case->due_date?->format('d/m/Y'),
            $case->requester,
            $case->affected_user,
            $case->position,
            $case->requestType?->name,
            $case->application?->name,
            $case->profile?->name,
            $case->permission,
            $case->group?->name,
            $case->groupFamily?->name,
            $case->priority?->name,
            $case->status?->name,
            $case->analyst?->name,
            $case->started_at?->format('d/m/Y H:i'),
            $case->finished_at?->format('d/m/Y H:i'),
            $case->concept,
            $case->result,
            $case->observations,
        ];
    }
}