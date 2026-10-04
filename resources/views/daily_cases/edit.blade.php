@extends('layouts.app')

@section('page-title', 'Editar caso')
@section('title', 'Editar caso — Flujo Diario')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <a href="{{ route('daily-cases.show', $case) }}" class="text-sm text-brand-600 hover:underline">&larr; Volver al caso</a>
            <h2 class="mt-1 text-xl font-bold text-gray-900">Editar caso {{ $case->case_number ?: '#'.$case->id }}</h2>
        </div>
    </div>

    @include('daily_cases._form', [
        'case' => $case,
        'action' => route('daily-cases.update', $case),
        'method' => 'PUT',
        'submitLabel' => 'Guardar cambios',
    ])
</div>
@endsection