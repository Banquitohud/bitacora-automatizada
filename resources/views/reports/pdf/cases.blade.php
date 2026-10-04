<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de flujo diario</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 18px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .meta { color: #6b7280; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        .kpis { display: flex; flex-wrap: wrap; gap: 8px; }
        .kpi { border: 1px solid #e5e7eb; padding: 8px 12px; border-radius: 6px; }
        .kpi b { display: block; font-size: 16px; }
        .kpi span { font-size: 9px; color: #6b7280; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de Flujo Diario</h1>
    <p class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</p>

    <div class="kpis">
        <div class="kpi"><b>{{ $flow['total_received'] }}</b><span>Recibidos</span></div>
        <div class="kpi"><b>{{ $flow['total_finished'] }}</b><span>Finalizados</span></div>
        <div class="kpi"><b>{{ $flow['total_pending'] }}</b><span>Pendientes</span></div>
        <div class="kpi"><b>{{ $flow['total_in_process'] }}</b><span>En proceso</span></div>
        <div class="kpi"><b>{{ $flow['total_overdue'] }}</b><span>Vencidos</span></div>
        <div class="kpi"><b>{{ $flow['avg_time_hours'] !== null ? number_format((float) $flow['avg_time_hours'], 2) : '—' }}</b><span>T. promedio (h)</span></div>
    </div>

    <h2>Detalle de casos ({{ $cases->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>N° Caso</th>
                <th>Recepción</th>
                <th>Solicitante</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Aplicación</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Analista</th>
                <th>F. límite</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cases as $case)
                <tr>
                    <td>{{ $case->internal_id }}</td>
                    <td>{{ $case->case_number ?? '—' }}</td>
                    <td>{{ $case->received_date?->format('d/m/Y') }}</td>
                    <td>{{ $case->requester ?? '—' }}</td>
                    <td>{{ $case->affected_user ?? '—' }}</td>
                    <td>{{ $case->requestType?->name ?? '—' }}</td>
                    <td>{{ $case->application?->name ?? '—' }}</td>
                    <td>{{ $case->priority?->name ?? '—' }}</td>
                    <td>{{ $case->status?->name ?? '—' }}</td>
                    <td>{{ $case->analyst?->name ?? '—' }}</td>
                    <td>{{ $case->due_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="11">Sin registros para los filtros aplicados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>