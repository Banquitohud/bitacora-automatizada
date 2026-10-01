@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('title', 'Dashboard')

@section('content')
@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');
@endphp

<div class="space-y-6">

    {{-- Encabezado + acciones rápidas --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $greeting }}, {{ auth()->user()->name }}</h2>
            <p class="text-sm text-gray-500">
                Resumen actualizado en tiempo real · <span class="font-medium text-gray-700">{{ $periodRange['from']->format('d/m/Y') }} — {{ $periodRange['to']->format('d/m/Y') }}</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('daily-cases.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">+ Nuevo caso</a>
            <a href="{{ route('projects.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">+ Nuevo proyecto</a>
            <a href="{{ route('daily-cases.index', ['flag' => 'red']) }}" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Ver vencidos</a>
            <a href="{{ route('daily-cases.index', ['flag' => 'yellow']) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">Próximos a vencer</a>
            <a href="{{ route('reports.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Ver reportes</a>
        </div>
    </div>

    {{-- Filtros globales --}}
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500">Periodo</label>
            <select name="period" onchange="this.form.submit()" class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                @foreach(['today' => 'Hoy', 'yesterday' => 'Ayer', 'last7' => 'Últimos 7 días', 'month' => 'Este mes', 'lastMonth' => 'Mes anterior', 'year' => 'Este año'] as $key => $label)
                    <option value="{{ $key }}" {{ $period === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
                <option value="custom" {{ !in_array($period, ['today','yesterday','last7','month','lastMonth','year']) ? 'selected' : '' }}>Personalizado</option>
            </select>
        </div>

        @if(!in_array($period, ['today','yesterday','last7','month','lastMonth','year']))
            <div>
                <label class="block text-xs font-medium text-gray-500">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
        @endif

        @if(auth()->user()->isAdmin())
            <div>
                <label class="block text-xs font-medium text-gray-500">Analista</label>
                <select name="analyst_id" onchange="this.form.submit()" class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($analysts as $analyst)
                        <option value="{{ $analyst->id }}" {{ (string) $analystId === (string) $analyst->id ? 'selected' : '' }}>{{ $analyst->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="sm:ml-auto">
            <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Limpiar</a>
        </div>
    </form>

    {{-- KPIs Flujo Diario --}}
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Flujo diario</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <x-kpi-card label="Recibidos (periodo)" :value="$kpis['received_period']" color="brand"/>
            <x-kpi-card label="Recibidos hoy" :value="$kpis['received_today']" color="blue"/>
            <x-kpi-card label="Pendientes" :value="$kpis['pending']" color="amber"/>
            <x-kpi-card label="En proceso" :value="$kpis['in_process']" color="blue"/>
            <x-kpi-card label="Finalizados (periodo)" :value="$kpis['finished_period']" color="green"/>
            <x-kpi-card label="Vencidos" :value="$kpis['overdue']" color="red"/>
            <x-kpi-card label="Próximos a vencer" :value="$kpis['near_due']" color="amber"/>
            <x-kpi-card label="Prioridad alta/crítica" :value="$kpis['high_priority']" color="red"/>
            <x-kpi-card label="Esta semana" :value="$kpis['received_week']" color="gray"/>
            <x-kpi-card label="Este mes" :value="$kpis['received_month']" color="gray"/>
            <x-kpi-card label="Total registrado" :value="$kpis['total_cases']" color="gray"/>
        </div>
    </div>

    {{-- KPIs Proyectos --}}
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Proyectos</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <x-kpi-card label="Proyectos activos" :value="$projectKpis['active']" color="brand"/>
            <x-kpi-card label="En planeación" :value="$projectKpis['pending']" color="amber"/>
            <x-kpi-card label="Finalizados" :value="$projectKpis['finished']" color="green"/>
            <x-kpi-card label="Próximos a vencer" :value="$projectKpis['near_due']" color="amber"/>
            <x-kpi-card label="Vencidos" :value="$projectKpis['overdue']" color="red"/>
            <x-kpi-card label="Avance promedio" :value="$projectKpis['avg_progress'].'%'" color="blue"/>
        </div>
    </div>

    {{-- Gráficas --}}
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Gráficas</h3>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos recibidos por día</h4>
                <canvas id="chartReceived" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos por estado</h4>
                <canvas id="chartStatus" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos por tipo de solicitud</h4>
                <canvas id="chartType" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos gestionados por analista</h4>
                <canvas id="chartAnalyst" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos por aplicación</h4>
                <canvas id="chartApp" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Proyectos por estado</h4>
                <canvas id="chartProjStatus" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Avance porcentual de proyectos</h4>
                <canvas id="chartProjProgress" height="110"></canvas>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">Casos cerrados por día</h4>
                <canvas id="chartClosed" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- Productividad --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Productividad por analista</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Analista</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Recibidos</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Finalizados</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Pendientes</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">T. promedio (h)</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">T. total (h)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($productivity['by_analyst'] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-800">{{ $row['user']->name }}</td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ $row['received'] }}</td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-600">{{ $row['finished'] }}</td>
                            <td class="px-5 py-3 text-right font-medium text-amber-600">{{ $row['pending'] }}</td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ $row['avg_hours'] !== null ? number_format($row['avg_hours'], 2) : '—' }}</td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ $row['total_hours'] !== null ? number_format($row['total_hours'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-gray-400">Sin datos para el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Mis casos --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Mis casos</h3>
                <a href="{{ route('daily-cases.index') }}" class="text-xs font-medium text-brand-600 hover:underline">Ver todos</a>
            </div>
            <div class="grid grid-cols-3 gap-3 p-5">
                <div class="rounded-lg bg-amber-50 p-3 text-center">
                    <p class="text-xl font-bold text-amber-600">{{ $myCases['pending'] }}</p>
                    <p class="text-xs text-gray-500">Pendientes</p>
                </div>
                <div class="rounded-lg bg-blue-50 p-3 text-center">
                    <p class="text-xl font-bold text-blue-600">{{ $myCases['in_analysis'] + $myCases['in_management'] }}</p>
                    <p class="text-xs text-gray-500">En proceso</p>
                </div>
                <div class="rounded-lg bg-red-50 p-3 text-center">
                    <p class="text-xl font-bold text-red-600">{{ $myCases['overdue'] }}</p>
                    <p class="text-xs text-gray-500">Vencidos</p>
                </div>
            </div>
            <ul class="max-h-72 divide-y divide-gray-100 overflow-y-auto px-2 pb-2">
                @forelse($myCases['list'] as $case)
                    <li>
                        <a href="{{ route('daily-cases.show', $case) }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">{{ $case->case_number ?: '#'.$case->id }}</span>
                            <span class="text-xs text-gray-400">{{ $case->status?->name ?? 'Sin estado' }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-3 py-6 text-center text-sm text-gray-400">No tienes casos asignados.</li>
                @endforelse
            </ul>
        </div>

        {{-- Mis proyectos --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Mis proyectos</h3>
                <a href="{{ route('projects.index') }}" class="text-xs font-medium text-brand-600 hover:underline">Ver todos</a>
            </div>
            <div class="grid grid-cols-3 gap-3 p-5">
                <div class="rounded-lg bg-brand-50 p-3 text-center">
                    <p class="text-xl font-bold text-brand-700">{{ $myProjects['active'] }}</p>
                    <p class="text-xs text-gray-500">Activos</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-3 text-center">
                    <p class="text-xl font-bold text-amber-600">{{ $myProjects['pending_tasks'] }}</p>
                    <p class="text-xs text-gray-500">Tareas pend.</p>
                </div>
                <div class="rounded-lg bg-red-50 p-3 text-center">
                    <p class="text-xl font-bold text-red-600">{{ $myProjects['near_due'] }}</p>
                    <p class="text-xs text-gray-500">Próx. vencer</p>
                </div>
            </div>
            <ul class="max-h-72 divide-y divide-gray-100 overflow-y-auto px-2 pb-2">
                @forelse($myProjects['list'] as $project)
                    <li>
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-700">{{ \Illuminate\Support\Str::limit($project->name, 40) }}</span>
                            <span class="text-xs text-gray-400">{{ $project->status?->name }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-3 py-6 text-center text-sm text-gray-400">No tienes proyectos asignados.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Actividad reciente</h3>
        </div>
        <div class="max-h-80 overflow-y-auto p-5">
            <ol class="relative space-y-4 border-l border-gray-200 pl-5">
                @forelse($recentActivities as $activity)
                    <li class="relative">
                        <span class="absolute -left-[27px] flex h-3 w-3 items-center justify-center rounded-full bg-brand-100 ring-4 ring-white"></span>
                        <div class="flex flex-wrap items-center gap-x-2">
                            <p class="text-sm text-gray-800">
                                <span class="font-semibold">{{ $activity['user'] }}</span>
                                {{ $activity['description'] }}
                            </p>
                            @if($activity['link'])
                                <a href="{{ $activity['link'] }}" class="text-xs text-brand-600 hover:underline">ver</a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400">{{ $activity['time']->format('d/m/Y H:i') }}</p>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">Sin actividad registrada.</li>
                @endforelse
            </ol>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const brand = '#2563eb';
    const palette = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#6b7280','#84cc16'];

    function pie(el, labels, data, colors) {
        const ctx = document.getElementById(el);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: colors || palette }]
            },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
    }

    function bar(el, labels, data, color) {
        const ctx = document.getElementById(el);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Cantidad', data, backgroundColor: color || brand, borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const receivedLabels = @json($charts['received_labels']);
    const receivedData = @json($charts['received_data']);
    bar('chartReceived', receivedLabels, receivedData, 'rgba(37,99,235,0.7)');

    const closed = @json($charts['closed_by_day']);
    bar('chartClosed', closed['labels'] ?? [], closed['data'] ?? [], 'rgba(16,185,129,0.7)');

    const status = @json($productivity['cases_by_status']);
    pie('chartStatus', Object.keys(status), Object.values(status));

    const types = @json($productivity['cases_by_type']);
    pie('chartType', Object.keys(types), Object.values(types));

    const analysts = @json($productivity['cases_by_analyst']);
    bar('chartAnalyst', Object.keys(analysts), Object.values(analysts), 'rgba(139,92,246,0.7)');

    const apps = @json($productivity['cases_by_application']);
    bar('chartApp', Object.keys(apps), Object.values(apps), 'rgba(6,182,212,0.7)');

    const projStatus = @json($charts['projects_by_status']);
    pie('chartProjStatus', Object.keys(projStatus), Object.values(projStatus));

    const projProgress = @json($charts['project_progress']);
    bar('chartProjProgress', projProgress['labels'] ?? [], projProgress['data'] ?? [], 'rgba(245,158,11,0.7)');
});
</script>
@endpush