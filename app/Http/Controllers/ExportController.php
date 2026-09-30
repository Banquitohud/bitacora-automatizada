<?php

namespace App\Http\Controllers;

use App\Exports\CasesExport;
use App\Models\DailyCase;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private readonly ReportService $report)
    {
    }

    public function export(Request $request, string $format): Response|StreamedResponse
    {
        $filters = $request->only(['from', 'to', 'analyst_id', 'status_id', 'request_type_id', 'application_id', 'priority_id', 'project_id']);

        $filename = 'reporte_'.now()->format('Ymd_His');

        return match ($format) {
            'xlsx' => Excel::download(new CasesExport($filters), $filename.'.xlsx'),
            'csv' => $this->csv($filters, $filename),
            'pdf' => $this->pdf($filters, $filename),
            default => abort(404),
        };
    }

    public function monthlyPdf(Request $request): Response
    {
        $month = $request->input('month', now()->format('Y-m'));
        $summary = $this->report->monthlySummary($month);

        $pdf = Pdf::loadView('reports.pdf.monthly', ['summary' => $summary])
            ->setPaper('a4', 'landscape');

        return $pdf->download('informe_mensual_'.$month.'.pdf');
    }

    protected function csv(array $filters, string $filename): StreamedResponse
    {
        $cases = $this->report->filteredCaseQuery($filters)->get();

        return response()->streamDownload(function () use ($cases) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel

            fputcsv($handle, [
                'ID', 'N° Caso', 'Fecha recepción', 'Hora', 'Fecha límite', 'Solicitante',
                'Usuario afectado', 'Cargo', 'Tipo solicitud', 'Aplicación', 'Perfil',
                'Permiso', 'Grupo', 'Familia', 'Prioridad', 'Estado', 'Analista',
                'Fecha inicio', 'Fecha fin', 'Concepto GSI', 'Resultado', 'Observaciones',
            ]);

            foreach ($cases as $case) {
                fputcsv($handle, [
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
                ]);
            }

            fclose($handle);
        }, $filename.'.csv');
    }

    protected function pdf(array $filters, string $filename): Response
    {
        $cases = $this->report->filteredCaseQuery($filters)->get();
        $flow = $this->report->flowReport($filters);

        $pdf = Pdf::loadView('reports.pdf.cases', ['cases' => $cases, 'flow' => $flow])
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename.'.pdf');
    }
}