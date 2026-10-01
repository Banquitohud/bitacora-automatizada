@extends('layouts.app')

@section('page-title', 'Flujo Diario')
@section('title', 'Flujo Diario')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Casos del flujo diario</h2>
            <p class="text-sm text-gray-500">Registra y haz seguimiento de los casos, solicitudes y requerimientos.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('import.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Importar Excel/CSV</a>
            <a href="{{ route('daily-cases.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">+ Nuevo caso</a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('daily-cases.index') }}" class="grid grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-4 xl:grid-cols-6">
        <div class="col-span-2 sm:col-span-2">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar ticket, usuario, solicitante, aplicación, perfil, grupo…"
                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <select name="status_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Estado: todos</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ (string) $filters['status_id'] === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="request_type_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Tipo: todos</option>
                @foreach($requestTypes as $type)
                    <option value="{{ $type->id }}" {{ (string) $filters['request_type_id'] === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="application_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Aplicación: todas</option>
                @foreach($applications as $app)
                    <option value="{{ $app->id }}" {{ (string) $filters['application_id'] === (string) $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="priority_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Prioridad: todas</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ (string) $filters['priority_id'] === (string) $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="analyst_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Analista: todos</option>
                @foreach($analysts as $analyst)
                    <option value="{{ $analyst->id }}" {{ (string) $filters['analyst_id'] === (string) $analyst->id ? 'selected' : '' }}>{{ $analyst->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="project_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Proyecto: todos</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (string) $filters['project_id'] === (string) $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="flag" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Semáforo: todos</option>
                <option value="green" {{ $filters['flag'] === 'green' ? 'selected' : '' }}>🟢 Dentro del tiempo</option>
                <option value="yellow" {{ $filters['flag'] === 'yellow' ? 'selected' : '' }}>🟡 Próximo a vencer</option>
                <option value="red" {{ $filters['flag'] === 'red' ? 'selected' : '' }}>🔴 Vencido</option>
                <option value="white" {{ $filters['flag'] === 'white' ? 'selected' : '' }}>⚪ Sin fecha límite</option>
            </select>
        </div>
        <div>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" title="Desde">
        </div>
        <div>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" title="Hasta">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
            <a href="{{ route('daily-cases.index') }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600">Limpiar</a>
        </div>
    </form>

    {{-- Tabla de casos --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Caso</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Recepción</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Solicitud</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aplicación</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Prioridad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Analista</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SLA</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cases as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('daily-cases.show', $case) }}" class="font-semibold text-brand-700 hover:underline">
                                    {{ $case->case_number ?: '#'.$case->id }}
                                </a>
                                @if($case->internal_id)
                                    <p class="text-xs text-gray-400">{{ $case->internal_id }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                {{ $case->received_date?->format('d/m/Y') }}
                                @if($case->received_time)<span class="text-xs text-gray-400">{{ $case->received_time?->format('H:i') }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $case->requestType?->name ?? '—' }}
                                @if($case->permission)<p class="text-xs text-gray-400">{{ $case->permission }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $case->application?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $case->affected_user ?: '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($case->priority)
                                    <x-badge :color="match($case->priority->slug) { 'alta' => 'amber', 'critica' => 'red', 'media' => 'blue', default => 'gray' }">
                                        {{ $case->priority->name }}
                                    </x-badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-badge :color="'gray'">{{ $case->status?->name ?? 'Sin estado' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $case->analyst?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span title="{{ match($case->flag()) { 'red' => 'Vencido', 'yellow' => 'Próximo a vencer', 'green' => 'Dentro del tiempo', 'white' => 'Sin fecha límite', 'closed' => 'Cerrado' } }}">
                                    {{ match($case->flag()) { 'red' => '🔴', 'yellow' => '🟡', 'green' => '🟢', 'white' => '⚪', 'closed' => '✅' } }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('daily-cases.show', $case) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                No se encontraron casos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-4 py-3">
            {{ $cases->links() }}
        </div>
    </div>
</div>
@endsection