@extends('layouts.app')

@section('page-title', 'Editar proyecto')
@section('title', 'Editar proyecto')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-5">
        <a href="{{ route('projects.show', $project) }}" class="text-sm text-brand-600 hover:underline">&larr; Volver al proyecto</a>
        <h2 class="mt-1 text-xl font-bold text-gray-900">Editar: {{ $project->name }}</h2>
    </div>

    @include('projects._form', [
        'project' => $project,
        'action' => route('projects.update', $project),
        'method' => 'PUT',
        'submitLabel' => 'Guardar cambios',
    ])
</div>
@endsection