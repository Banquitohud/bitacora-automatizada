@extends('layouts.app')

@section('page-title', $project->name)
@section('title', 'Proyecto')

@section('content')
<div class="space-y-5">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $project->name }}</h2>
            <p class="text-sm text-gray-500">
                {{ $project->code ? $project->code.' · ' : '' }}{{ $project->status?->name ?? 'Sin estado' }}
                @if($project->responsible) · Resp: {{ $project->responsible->name }}@endif
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($project->priority)
                    <x-badge :color="match($project->priority->slug) { 'alta' => 'amber', 'critica' => 'red', 'media' => 'blue', default => 'gray' }">{{ $project->priority->name }}</x-badge>
                @endif
                @if($project->isOverdue())
                    <x-badge color="red">🔴 Vencido</x-badge>
                @elseif($project->isNearDue())
                    <x-badge color="amber">🟡 Próximo a vencer</x-badge>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar proyecto</a>
            @endcan
            @can('delete', $project)
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('¿Eliminar este proyecto y sus tareas? (soft delete)')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        <x-kpi-card label="Total tareas" :value="$project->tasks->count()" color="gray"/>
        <x-kpi-card label="Completadas" :value="$project->tasks->filter(fn($t) => $t->isCompleted())->count()" color="green"/>
        <x-kpi-card label="Pendientes" :value="$project->tasks->filter(fn($t) => !$t->isCompleted())->count()" color="amber"/>
        <x-kpi-card label="Tareas vencidas" :value="$project->tasks->filter(fn($t) => $t->isOverdue())->count()" color="red"/>
        <x-kpi-card label="Casos asociados" :value="$project->cases->count()" color="blue"/>
        <x-kpi-card label="Avance" :value="$project->progress.'%'" color="brand"/>
    </div>

    {{-- Barra de progreso --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-1 flex items-center justify-between text-sm">
            <span class="font-medium text-gray-700">Avance del proyecto</span>
            <span class="font-semibold text-gray-900">{{ $project->progress }}%</span>
        </div>
        <div class="h-3 w-full rounded-full bg-gray-100">
            <div class="h-3 rounded-full bg-gradient-to-r from-brand-500 to-brand-700 transition-all" style="width: {{ $project->progress }}%"></div>
        </div>
    </div>

    {{-- Tareas del proyecto --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Tareas del proyecto ({{ $project->tasks->count() }})</h3>
            @can('update', $project)
                <button type="button" x-data @click="$refs.taskForm.showModal()" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">+ Agregar tarea</button>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tarea</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Responsable</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Inicio</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Límite</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Avance</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($project->tasks as $task)
                        <tr class="hover:bg-gray-50" x-data>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $task->name }}</p>
                                @if($task->description)<p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($task->description, 60) }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $task->responsible?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-badge :color="match($task->status?->slug) { 'completada' => 'green', 'en-progreso' => 'blue', 'en-revision' => 'purple', 'en-pausa' => 'amber', default => 'gray' }">{{ $task->status?->name ?? '—' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-500">{{ $task->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-500">
                                {{ $task->due_date?->format('d/m/Y') ?? '—' }}
                                @if($task->isOverdue())<span class="text-red-600"> 🔴</span>@endif
                                @if($task->completed_date)<span class="text-emerald-600"> ✅</span>@endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-20 rounded-full bg-gray-100">
                                        <div class="h-1.5 rounded-full {{ $task->progress >= 100 ? 'bg-emerald-500' : 'bg-brand-600' }}" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                @can('update', $project)
                                    <button type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50"
                                            @click="$refs.taskEdit{{ $task->id }}.showModal()">Editar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-6 text-center text-gray-400">Sin tareas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Casos del flujo diario en este proyecto ({{ $project->cases->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Caso</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Analista</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($project->cases as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('daily-cases.show', $case) }}" class="font-medium text-brand-700 hover:underline">{{ $case->case_number ?: '#'.$case->id }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $case->requestType?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $case->status?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $case->analyst?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">Sin casos asociados a este proyecto.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Historial --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Historial</h3></div>
        <ol class="max-h-64 space-y-3 overflow-y-auto p-5">
            @forelse($history as $entry)
                <li class="relative border-l border-gray-200 pl-4">
                    <span class="absolute -left-1 top-1 h-2 w-2 rounded-full bg-brand-500"></span>
                    <p class="text-sm text-gray-700"><span class="font-semibold">{{ $entry->user?->name ?? 'Sistema' }}</span> {{ $entry->description }}</p>
                    <p class="text-xs text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</p>
                </li>
            @empty
                <li class="text-sm text-gray-400">Sin actividad registrada.</li>
            @endforelse
        </ol>
    </div>
</div>

{{-- Modal agregar tarea --}}
@can('update', $project)
<dialog x-ref="taskForm" class="rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl">
    <form method="POST" action="{{ route('project-tasks.store', $project) }}" class="w-full max-w-lg p-6">
        @csrf
        <h3 class="text-lg font-semibold text-gray-900">Nueva tarea</h3>
        <div class="mt-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Responsable</label>
                    <select name="responsible_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="task_status_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($taskStatuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prioridad</label>
                    <select name="priority_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">— Seleccionar —</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Avance (%)</label>
                    <input type="number" name="progress" min="0" max="100" value="0" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha inicial</label>
                    <input type="date" name="start_date" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha límite</label>
                    <input type="date" name="due_date" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                <textarea name="comments" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>
        </div>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" @click="$refs.taskForm.close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600">Cancelar</button>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Agregar</button>
        </div>
    </form>
</dialog>

@foreach($project->tasks as $task)
    <dialog x-ref="taskEdit{{ $task->id }}" class="rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl">
        <form method="POST" action="{{ route('project-tasks.update', [$project, $task]) }}" class="w-full max-w-lg p-6">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-900">Editar tarea</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="name" required value="{{ $task->name }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $task->description }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Responsable</label>
                        <select name="responsible_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $task->responsible_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select name="task_status_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach($taskStatuses as $status)
                                <option value="{{ $status->id }}" {{ $task->task_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prioridad</label>
                        <select name="priority_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">— Seleccionar —</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ $task->priority_id == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Avance (%)</label>
                        <input type="number" name="progress" min="0" max="100" value="{{ $task->progress }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha inicial</label>
                        <input type="date" name="start_date" value="{{ $task->start_date?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha límite</label>
                        <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha finalización</label>
                        <input type="date" name="completed_date" value="{{ $task->completed_date?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                    <textarea name="comments" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $task->comments }}</textarea>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <button type="button" @click="$refs.taskEdit{{ $task->id }}.close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Guardar</button>
                    <button type="submit" form="deleteTask{{ $task->id }}" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Eliminar</button>
                </div>
            </div>
        </form>
        <form id="deleteTask{{ $task->id }}" method="POST" action="{{ route('project-tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('¿Eliminar esta tarea?')">
            @csrf
            @method('DELETE')
        </form>
    </dialog>
@endforeach
@endcan
@endsection