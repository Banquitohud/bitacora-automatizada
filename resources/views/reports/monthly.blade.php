@extends('layouts.app')

@section('page-title', 'Resumen mensual')
@section('title', 'Resumen mensual')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Resumen mensual</h2>
            <p class="text-sm text-gray-500">Indicadores agregados del mes seleccionado.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('reports.monthly') }}" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </form>
            <a href="{{ route('reports.monthly.pdf', ['month' => $month]) }}" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Exportar informe mensual (PDF)</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-kpi-card label="Casos recibidos" :value="$summary['total_received']" color="brand"/>
        <x-kpi-card label="Casos cerrados" :value="$summary['total_finished']" color="green"/>
        <x-kpi-card label="Pendientes" :value="$summary['total_pending']" color="amber"/>
        <x-kpi-card label="Vencidos" :value="$summary['total_overdue']" color="red"/>
        <x-kpi-card label="T. promedio (h)" :value="$summary['avg_time_hours'] !== null ? number_format((float) $summary['avg_time_hours'], 2) : '—'" color="gray"/>
        <x-kpi-card label="Proyectos activos" :value="$summary['active_projects']" color="blue"/>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos por analista</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($summary['by_analyst'] as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3"><span class="text-sm text-gray-700">{{ $name }}</span><x-badge color="brand">{{ $count }}</x-badge></li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos por aplicación</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($summary['by_application'] as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3"><span class="text-sm text-gray-700">{{ $name }}</span><x-badge color="blue">{{ $count }}</x-badge></li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos por tipo</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($summary['by_type'] as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3"><span class="text-sm text-gray-700">{{ $name }}</span><x-badge color="purple">{{ $count }}</x-badge></li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Proyectos del mes --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="grid grid-cols-2 gap-3 border-b border-gray-100 p-5 sm:grid-cols-4">
            <div class="rounded-lg bg-emerald-50 p-3 text-center">
                <p class="text-xl font-bold text-emerald-600">{{ $summary['finished_projects'] }}</p>
                <p class="text-xs text-gray-500">Proyectos finalizados</p>
            </div>
            <div class="rounded-lg bg-brand-50 p-3 text-center">
                <p class="text-xl font-bold text-brand-700">{{ $summary['active_projects'] }}</p>
                <p class="text-xs text-gray-500">Proyectos activos</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center">
                <p class="text-xl font-bold text-gray-700">{{ $summary['avg_project_progress'] !== null ? round($summary['avg_project_progress']).'%' : '—' }}</p>
                <p class="text-xs text-gray-500">Avance promedio</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Proyecto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Responsable</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Avance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summary['projects'] as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><a href="{{ route('projects.show', $project) }}" class="font-medium text-brand-700 hover:underline">{{ $project->name }}</a></td>
                            <td class="px-4 py-3 text-gray-600">{{ $project->status?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $project->responsible?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $project->progress }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Sin proyectos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection