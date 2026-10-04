<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe mensual {{ $summary['month']->format('m/Y') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0; }
        h2 { font-size: 12px; margin: 16px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .meta { color: #6b7280; margin: 4px 0 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
        .kpis { display: flex; flex-wrap: wrap; gap: 8px; }
        .kpi { border: 1px solid #e5e7eb; padding: 8px 12px; border-radius: 6px; }
        .kpi b { display: block; font-size: 15px; }
        .kpi span { font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Informe Mensual — {{ $summary['month']->locale('es')->isoFormat('MMMM YYYY') }}</h1>
    <p class="meta">Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}</p>

    <div class="kpis">
        <div class="kpi"><b>{{ $summary['total_received'] }}</b><span>Recibidos</span></div>
        <div class="kpi"><b>{{ $summary['total_finished'] }}</b><span>Cerrados</span></div>
        <div class="kpi"><b>{{ $summary['total_pending'] }}</b><span>Pendientes</span></div>
        <div class="kpi"><b>{{ $summary['total_overdue'] }}</b><span>Vencidos</span></div>
        <div class="kpi"><b>{{ $summary['avg_time_hours'] !== null ? number_format((float) $summary['avg_time_hours'], 2) : '—' }}</b><span>T. promedio (h)</span></div>
        <div class="kpi"><b>{{ $summary['active_projects'] }}</b><span>Proy. activos</span></div>
        <div class="kpi"><b>{{ $summary['finished_projects'] }}</b><span>Proy. finalizados</span></div>
        <div class="kpi"><b>{{ $summary['avg_project_progress'] !== null ? round($summary['avg_project_progress']).'%' : '—' }}</b><span>Avance prom. proy.</span></div>
    </div>

    <h2>Casos por analista</h2>
    <table>
        <thead><tr><th>Analista</th><th>Casos</th></tr></thead>
        <tbody>
            @forelse($summary['by_analyst'] as $name => $count)
                <tr><td>{{ $name }}</td><td>{{ $count }}</td></tr>
            @empty
                <tr><td colspan="2">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Casos por aplicación</h2>
    <table>
        <thead><tr><th>Aplicación</th><th>Casos</th></tr></thead>
        <tbody>
            @forelse($summary['by_application'] as $name => $count)
                <tr><td>{{ $name }}</td><td>{{ $count }}</td></tr>
            @empty
                <tr><td colspan="2">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Casos por tipo de solicitud</h2>
    <table>
        <thead><tr><th>Tipo</th><th>Casos</th></tr></thead>
        <tbody>
            @forelse($summary['by_type'] as $name => $count)
                <tr><td>{{ $name }}</td><td>{{ $count }}</td></tr>
            @empty
                <tr><td colspan="2">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Proyectos del mes</h2>
    <table>
        <thead><tr><th>Proyecto</th><th>Estado</th><th>Avance</th></tr></thead>
        <tbody>
            @forelse($summary['projects'] as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->status?->name ?? '—' }}</td>
                    <td>{{ $project->progress }}%</td>
                </tr>
            @empty
                <tr><td colspan="3">Sin proyectos.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>