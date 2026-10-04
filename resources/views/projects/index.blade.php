@extends('layouts.app')

@section('page-title', 'Proyectos')
@section('title', 'Proyectos')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Proyectos del área GSI</h2>
            <p class="text-sm text-gray-500">Trabajos especiales, iniciativas, depuraciones, migraciones y análisis masivos.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">+ Nuevo proyecto</a>
    </div>

    <form method="GET" action="{{ route('projects.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-4">
        <div class="sm:col-span-2">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar nombre o código…" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <select name="project_status_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Estado: todos</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ (string) $filters['project_status_id'] === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="responsible_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Responsable: todos</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) $filters['responsible_id'] === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
            <a href="{{ route('projects.index') }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600">Limpiar</a>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($projects as $project)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('projects.show', $project) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-700">{{ $project->name }}</a>
                        @if($project->code)<p class="text-xs text-gray-400">{{ $project->code }}</p>@endif
                    </div>
                    @if($project->status)
                        <x-badge :color="match($project->status->slug) { 'finalizado' => 'green', 'cancelado' => 'gray', 'en-ejecucion' => 'blue', 'en-revision' => 'purple', 'en-pausa' => 'amber', default => 'gray' }">{{ $project->status->name }}</x-badge>
                    @endif
                </div>

                <p class="mt-3 line-clamp-2 text-sm text-gray-500">{{ $project->description ?: 'Sin descripción.' }}</p>

                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-gray-500">Avance</span>
                        <span class="font-semibold text-gray-700">{{ $project->progress }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-brand-600" style="width: {{ $project->progress }}%"></div>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                    <span>{{ $project->responsible?->name ?? 'Sin responsable' }}</span>
                    <span title="Fecha estimada">{{ $project->estimated_end_date?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-400 md:col-span-2 xl:col-span-3">
                No hay proyectos registrados.
            </div>
        @endforelse
    </div>

    <div>
        {{ $projects->links() }}
    </div>
</div>
@endsection