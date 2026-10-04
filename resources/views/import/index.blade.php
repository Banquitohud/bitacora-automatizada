@extends('layouts.app')

@section('page-title', 'Importar casos')
@section('title', 'Importar casos')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">

    <div>
        <h2 class="text-xl font-bold text-gray-900">Importar casos desde Excel/CSV</h2>
        <p class="text-sm text-gray-500">Permite cargar datos históricos. Se valida y muestra una vista previa antes de importar.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Archivo</h3>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required
                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">Formatos: XLSX, XLS, CSV · Máx 10MB</p>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Previsualizar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Equivalencias de columnas</h3>
        </div>
        <div class="overflow-x-auto p-5">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Columna en el archivo</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Campo del sistema</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($expectedHeaders as $index => $header)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-700">{{ $header }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $index === 0 ? 'Número de caso (obligatorio, único)' : ($index === 1 ? 'Fecha de recepción (obligatoria)' : 'Opcional') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3 text-xs text-gray-400">
                Los valores de tipo, aplicación, perfil, grupo, familia, prioridad, estado y analista se relacionan por nombre.
                Si no existen en los catálogos, el campo se deja vacío (los errados se reportan en la vista previa).
            </p>
        </div>
    </div>
</div>
@endsection