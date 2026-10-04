@extends('layouts.app')

@section('page-title', 'Reportes')
@section('title', 'Reportes')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Reportes</h2>
            <p class="text-sm text-gray-500">Indicadores calculados automáticamente desde los registros.</p>
        </div>
        <a href="{{ route('reports.monthly') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Resumen mensual</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-6">
        <div>
            <label class="block text-xs font-medium text-gray-500">Fecha inicial</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Fecha final</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Analista</label>
            <select name="analyst_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach($analystList as $user)
                    <option value="{{ $user->id }}" {{ (string) $filters['analyst_id'] === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Estado</label>
            <select name="status_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ (string) $filters['status_id'] === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Tipo de solicitud</label>
            <select name="request_type_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach($requestTypeList as $type)
                    <option value="{{ $type->id }}" {{ (string) $filters['request_type_id'] === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Aplicación</label>
            <select name="application_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todas</option>
                @foreach($applicationList as $app)
                    <option value="{{ $app->id }}" {{ (string) $filters['application_id'] === (string) $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Prioridad</label>
            <select name="priority_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todas</option>
                @foreach($priorityList as $priority)
                    <option value="{{ $priority->id }}" {{ (string) $filters['priority_id'] === (string) $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2 lg:col-span-1">
            <label class="block text-xs font-medium text-gray-500">Proyecto</label>
            <select name="project_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach($projectList as $project)
                    <option value="{{ $project->id }}" {{ (string) $filters['project_id'] === (string) $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2 flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Generar</button>
            <a href="{{ route('reports.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600">Limpiar</a>
            <span class="ml-auto flex gap-2">
                <a href="{{ route('reports.export', 'xlsx') }}?{{ http_build_query(array_filter($filters)) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100" title="Exportar Excel">Excel</a>
                <a href="{{ route('reports.export', 'csv') }}?{{ http_build_query(array_filter($filters)) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50" title="Exportar CSV">CSV</a>
                <a href="{{ route('reports.export', 'pdf') }}?{{ http_build_query(array_filter($filters)) }}" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100" title="Exportar PDF">PDF</a>
            </span>
        </div>
    </form>

    {{-- KPIs del reporte de flujo --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-kpi-card label="Recibidos" :value="$flow['total_received']" color="brand"/>
        <x-kpi-card label="Finalizados" :value="$flow['total_finished']" color="green"/>
        <x-kpi-card label="Pendientes" :value="$flow['total_pending']" color="amber"/>
        <x-kpi-card label="En proceso" :value="$flow['total_in_process']" color="blue"/>
        <x-kpi-card label="Vencidos" :value="$flow['total_overdue']" color="red"/>
        <x-kpi-card label="T. prom. (h)" :value="$flow['avg_time_hours'] !== null ? number_format((float) $flow['avg_time_hours'], 2) : '—'" color="gray"/>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Productividad --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Reporte de productividad</h3></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Analista</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Recibidos</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Finalizados</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Pendientes</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Prom (h)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Total (h)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($productivity as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row['user']->name }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row['received'] }}</td>
                                <td class="px-4 py-3 text-right font-medium text-emerald-600">{{ $row['finished'] }}</td>
                                <td class="px-4 py-3 text-right font-medium text-amber-600">{{ $row['pending'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row['avg_hours'] !== null ? number_format($row['avg_hours'], 2) : '—' }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row['total_hours'] !== null ? number_format($row['total_hours'], 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Por aplicación --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos por aplicación</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($applications as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-gray-700">{{ $name }}</span>
                        <x-badge color="blue">{{ $count }}</x-badge>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>

        {{-- Por tipo de solicitud --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos por tipo de solicitud</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($requestTypes as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-gray-700">{{ $name }}</span>
                        <x-badge color="brand">{{ $count }}</x-badge>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>

        {{-- Total por analista --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Total de casos por analista</h3></div>
            <ul class="divide-y divide-gray-100">
                @forelse($flow['total_by_analyst'] as $name => $count)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-gray-700">{{ $name }}</span>
                        <x-badge color="gray">{{ $count }}</x-badge>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-gray-400">Sin datos.</li>
                @endforelse
            </ul>
        </div>

        {{-- Reporte de proyectos --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Reporte de proyectos</h3></div>
            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-5">
                <div class="rounded-lg bg-brand-50 p-3 text-center">
                    <p class="text-xl font-bold text-brand-700">{{ $projects['active_count'] }}</p>
                    <p class="text-xs text-gray-500">Activos</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3 text-center">
                    <p class="text-xl font-bold text-emerald-600">{{ $projects['finished_count'] }}</p>
                    <p class="text-xs text-gray-500">Finalizados</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 text-center">
                    <p class="text-xl font-bold text-gray-700">{{ $projects['avg_progress'] !== null ? round($projects['avg_progress']).'%' : '—' }}</p>
                    <p class="text-xs text-gray-500">Avance prom.</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-3 text-center">
                    <p class="text-xl font-bold text-amber-600">{{ $projects['pending_tasks'] }}</p>
                    <p class="text-xs text-gray-500">Tareas pend.</p>
                </div>
                <div class="rounded-lg bg-red-50 p-3 text-center">
                    <p class="text-xl font-bold text-red-600">{{ $projects['overdue_tasks'] }}</p>
                    <p class="text-xs text-gray-500">Tareas vencidas</p>
                </div>
            </div>
            <div class="overflow-x-auto border-t border-gray-100">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Proyecto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Responsable</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Avance</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Tareas pend.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Casos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($projects['projects'] as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-brand-700 hover:underline">{{ $project->name }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $project->status?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $project->responsible?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $project->progress }}%</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $project->tasks->filter(fn($t) => !$t->isCompleted())->count() }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $project->cases_count ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection