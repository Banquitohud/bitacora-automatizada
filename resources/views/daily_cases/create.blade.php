@extends('layouts.app')

@section('page-title', 'Nuevo caso')
@section('title', 'Nuevo caso — Flujo Diario')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-5">
        <a href="{{ route('daily-cases.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Volver al flujo diario</a>
        <h2 class="mt-1 text-xl font-bold text-gray-900">Registrar nuevo caso</h2>
        <p class="text-sm text-gray-500">Completa las secciones. Solo la fecha de recepción es obligatoria.</p>
    </div>

    @include('daily_cases._form', [
        'case' => $case,
        'action' => route('daily-cases.store'),
        'method' => 'POST',
        'submitLabel' => 'Registrar caso',
    ])
</div>
@endsection