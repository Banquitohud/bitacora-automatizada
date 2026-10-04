@extends('layouts.app')

@section('page-title', 'Nuevo proyecto')
@section('title', 'Nuevo proyecto')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-5">
        <a href="{{ route('projects.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Volver a proyectos</a>
        <h2 class="mt-1 text-xl font-bold text-gray-900">Crear nuevo proyecto</h2>
    </div>

    @include('projects._form', [
        'project' => $project,
        'action' => route('projects.store'),
        'method' => 'POST',
        'submitLabel' => 'Crear proyecto',
    ])
</div>
@endsection