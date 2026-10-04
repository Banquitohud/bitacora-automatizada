@extends('layouts.app')

@section('page-title', 'Vista previa de importación')
@section('title', 'Importar casos')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Vista previa de importación</h2>
            <p class="text-sm text-gray-500">
                {{ $preview['valid_count'] }} fila(s) válidas · {{ $preview['error_count'] }} con errores/duplicados.
            </p>
        </div>
        <a href="{{ route('import.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Elegir otro archivo</a>
    </div>

    @if($preview['error_count'] > 0)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            ⚠️ Solo se importarán las filas válidas. Las filas con errores o duplicadas serán omitidas y listadas abajo.
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="max-h-96 overflow-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="sticky top-0 bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                        @foreach($preview['rows']->isNotEmpty() ? array_slice(array_keys($preview['rows']->first()['fields']), 0, 8) : [] as $field)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">{{ $field }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Errores</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($preview['rows'] as $row)
                        <tr class="{{ $row['is_valid'] ? '' : 'bg-red-50/50' }}">
                            <td class="px-4 py-2 text-gray-500">{{ $row['row_number'] }}</td>
                            @foreach(array_slice(array_values($row['fields']), 0, 8) as $value)
                                <td class="max-w-40 truncate px-4 py-2 text-gray-700">{{ $value }}</td>
                            @endforeach
                            <td class="whitespace-nowrap px-4 py-2">
                                @if($row['is_valid'])
                                    <x-badge color="green">Válida</x-badge>
                                @elseif($row['is_duplicate'])
                                    <x-badge color="amber">Duplicada</x-badge>
                                @else
                                    <x-badge color="red">Error</x-badge>
                                @endif
                            </td>
                            <td class="max-w-56 px-4 py-2 text-xs text-red-600">{{ implode(', ', $row['errors']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($preview['valid_count'] > 0)
        <form method="POST" action="{{ route('import.store') }}" class="flex justify-end gap-3">
            @csrf
            <input type="hidden" name="temporary_path" value="{{ $temporaryPath }}">
            <a href="{{ route('import.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Importar {{ $preview['valid_count'] }} fila(s) válidas
            </button>
        </form>
    @endif
</div>
@endsection
