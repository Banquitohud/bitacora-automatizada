@extends('layouts.app')

@section('page-title', 'Caso ' . ($case->case_number ?: '#'.$case->id))
@section('title', 'Caso — Flujo Diario')

@section('content')
@php
    $flag = $case->flag();
@endphp

<div class="space-y-5">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
            <span class="text-3xl">{{ match($flag) { 'red' => '🔴', 'yellow' => '🟡', 'green' => '🟢', 'white' => '⚪', 'closed' => '✅' } }}</span>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $case->case_number ?: '#'.$case->id }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $case->internal_id ? $case->internal_id.' · ' : '' }}{{ $case->requestType?->name ?? 'Sin tipo' }} · {{ $case->application?->name ?? 'Sin aplicación' }}
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if($case->status)
                        <x-badge color="gray">{{ $case->status->name }}</x-badge>
                    @endif
                    @if($case->priority)
                        <x-badge :color="match($case->priority->slug) { 'alta' => 'amber', 'critica' => 'red', 'media' => 'blue', default => 'gray' }">{{ $case->priority->name }}</x-badge>
                    @endif
                    @if($case->analyst)
                        <x-badge color="purple">Asignado a {{ $case->analyst->name }}</x-badge>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('update', $case)
                @if(! $case->isClosed())
                    <form method="POST" action="{{ route('daily-cases.take', $case) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100">Tomar caso</button>
                    </form>
                    <form method="POST" action="{{ route('daily-cases.close', $case) }}" x-data="{ open: false }">
                        @csrf
                        <button type="button" @click="open = true" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Finalizar caso</button>
                        <div x-cloak x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.outside="open = false">
                                <h3 class="text-lg font-semibold text-gray-900">Finalizar caso</h3>
                                <label class="mt-3 block text-sm font-medium text-gray-700">Resultado</label>
                                <textarea name="result" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Describe el resultado…"></textarea>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600">Cancelar</button>
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Confirmar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
                <a href="{{ route('daily-cases.edit', $case) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
            @endcan
            @can('delete', $case)
                <form method="POST" action="{{ route('daily-cases.destroy', $case) }}" onsubmit="return confirm('¿Eliminar este caso? Esta acción es reversible (soft delete).')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">

        {{-- Información del caso --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Información del caso</h3></div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 sm:grid-cols-2">
                <div><dt class="text-xs text-gray-400">Fecha de recepción</dt><dd class="text-sm font-medium text-gray-800">{{ $case->received_date?->format('d/m/Y') }} @if($case->received_time){{ $case->received_time->format('H:i') }}@endif</dd></div>
                <div><dt class="text-xs text-gray-400">Fecha límite</dt><dd class="text-sm font-medium text-gray-800">{{ $case->due_date?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Solicitante</dt><dd class="text-sm font-medium text-gray-800">{{ $case->requester ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Usuario afectado</dt><dd class="text-sm font-medium text-gray-800">{{ $case->affected_user ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Cargo</dt><dd class="text-sm font-medium text-gray-800">{{ $case->position ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Creado por</dt><dd class="text-sm font-medium text-gray-800">{{ $case->createdBy?->name ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- Solicitud --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Solicitud</h3></div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 sm:grid-cols-2">
                <div><dt class="text-xs text-gray-400">Tipo de solicitud</dt><dd class="text-sm font-medium text-gray-800">{{ $case->requestType?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Aplicación</dt><dd class="text-sm font-medium text-gray-800">{{ $case->application?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Perfil solicitado</dt><dd class="text-sm font-medium text-gray-800">{{ $case->profile?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Permiso / transacción</dt><dd class="text-sm font-medium text-gray-800">{{ $case->permission ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Grupo</dt><dd class="text-sm font-medium text-gray-800">{{ $case->group?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Familia de grupo</dt><dd class="text-sm font-medium text-gray-800">{{ $case->groupFamily?->name ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- Gestión --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Gestión</h3></div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 sm:grid-cols-3">
                <div><dt class="text-xs text-gray-400">Estado</dt><dd class="text-sm font-medium text-gray-800">{{ $case->status?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Prioridad</dt><dd class="text-sm font-medium text-gray-800">{{ $case->priority?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Analista</dt><dd class="text-sm font-medium text-gray-800">{{ $case->analyst?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Fecha de inicio</dt><dd class="text-sm font-medium text-gray-800">{{ $case->started_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Fecha de finalización</dt><dd class="text-sm font-medium text-gray-800">{{ $case->finished_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div>
                    <dt class="text-xs text-gray-400">Tiempos (h)</dt>
                    <dd class="text-sm font-medium text-gray-800">
                        Inicio: {{ $case->timeToStartHours() !== null ? number_format($case->timeToStartHours(), 1) : '—' }} ·
                        Gestión: {{ $case->handlingTimeHours() !== null ? number_format($case->handlingTimeHours(), 1) : '—' }} ·
                        Total: {{ $case->totalTimeHours() !== null ? number_format($case->totalTimeHours(), 1) : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Concepto GSI --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Concepto GSI</h3></div>
            <div class="p-5">
                @if($case->concept)
                    <div class="whitespace-pre-wrap text-sm text-gray-700">{{ $case->concept }}</div>
                @else
                    <p class="text-sm text-gray-400">Sin concepto registrado.</p>
                @endif
            </div>
        </div>

        {{-- Resultado final --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Resultado y observaciones</h3></div>
            <div class="space-y-3 p-5">
                <div><p class="text-xs text-gray-400">Resultado</p><p class="whitespace-pre-wrap text-sm text-gray-700">{{ $case->result ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-400">Observaciones</p><p class="whitespace-pre-wrap text-sm text-gray-700">{{ $case->observations ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-400">Comentarios</p><p class="whitespace-pre-wrap text-sm text-gray-700">{{ $case->comments ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Proyecto asociado --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Proyectos asociados</h3></div>
            <div class="p-5">
                @forelse($case->projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-3 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $project->name }}</p>
                            <p class="text-xs text-gray-400">{{ $project->code }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $project->progress }}%</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400">Este caso no está asociado a ningún proyecto.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Historial (columna derecha) --}}
    <div class="xl:col-span-1">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Historial de cambios</h3></div>
            <ol class="max-h-[640px] space-y-3 overflow-y-auto p-5">
                @forelse($history as $entry)
                    <li class="relative border-l border-gray-200 pl-4">
                        <span class="absolute -left-1 top-1 h-2 w-2 rounded-full bg-brand-500"></span>
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold">{{ $entry->user?->name ?? 'Sistema' }}</span>
                            {{ $entry->description }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</p>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">Sin actividad registrada.</li>
                @endforelse
            </ol>
        </div>
    </div>
    </div>
</div>
@endsection