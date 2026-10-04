@extends('layouts.app')

@section('page-title', 'Configuración')
@section('title', 'Configuración')

@php
    $tabs = [
        'case_statuses'   => ['label' => 'Estados',         'fields' => [['name','Nombre','text'],['color','Color','color'],['sort_order','Orden','number'],['is_active','Activo','checkbox'],['is_initial','Inicial','checkbox'],['is_closed','Cierre','checkbox']]],
        'request_types'   => ['label' => 'Tipos de solicitud','fields' => [['name','Nombre','text'],['sort_order','Orden','number'],['is_active','Activo','checkbox']]],
        'priorities'      => ['label' => 'Prioridades',     'fields' => [['name','Nombre','text'],['color','Color','color'],['sort_order','Orden','number'],['is_active','Activo','checkbox']]],
        'applications'    => ['label' => 'Aplicaciones',    'fields' => [['name','Nombre','text'],['code','Código','text'],['description','Descripción','text'],['is_active','Activo','checkbox']]],
        'profiles'        => ['label' => 'Perfiles',        'fields' => [['name','Nombre','text'],['description','Descripción','text']]],
        'groups'          => ['label' => 'Grupos',          'fields' => [['name','Nombre','text'],['group_family_id','Familia','select:group_families'],['description','Descripción','text']]],
        'group_families'  => ['label' => 'Familias',        'fields' => [['name','Nombre','text'],['description','Descripción','text']]],
        'project_statuses'=> ['label' => 'Estados de proyecto','fields' => [['name','Nombre','text'],['color','Color','color'],['sort_order','Orden','number'],['is_active','Activo','checkbox']]],
        'task_statuses'   => ['label' => 'Estados de tarea', 'fields' => [['name','Nombre','text'],['color','Color','color'],['sort_order','Orden','number'],['is_active','Activo','checkbox']]],
        'sla'             => ['label' => 'SLA',              'fields' => [['name','Nombre','text'],['unit','Unidad','select:units'],['value','Valor','number'],['applies_to','Aplica a','select:applies_to'],['request_type_id','Tipo','select:request_types'],['priority_id','Prioridad','select:priorities'],['is_active','Activo','checkbox']]],
    ];
    $activeTab = $activeTab ?? 'case_statuses';
    $config = $tabs[$activeTab] ?? $tabs['case_statuses'];
    $items = $catalogs[$activeTab] ?? collect();
@endphp

@section('content')
<div class="space-y-5">

    <div>
        <h2 class="text-xl font-bold text-gray-900">Configuración</h2>
        <p class="text-sm text-gray-500">Administra los catálogos del sistema. Los cambios se reflejan inmediatamente.</p>
    </div>

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-2">
        @foreach($tabs as $key => $tab)
            <a href="{{ route('settings.index', ['tab' => $key]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium {{ $activeTab === $key ? 'bg-brand-600 text-white' : 'border border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Panel del catálogo --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $config['label'] }}</h3>
            <span class="text-xs text-gray-400">{{ $items->count() }} registro(s)</span>
        </div>

        {{-- Formulario de alta --}}
        <form method="POST" action="{{ route('settings.store', $activeTab) }}" class="grid grid-cols-2 gap-3 border-b border-gray-100 bg-gray-50 p-4 sm:grid-cols-4 lg:grid-cols-6">
            @csrf
            @foreach($config['fields'] as $field)
                @php [$fieldKey, $fieldLabel, $fieldType] = $field; @endphp
                @if($fieldType === 'checkbox')
                    <label class="flex items-center gap-2 pt-6 text-xs font-medium text-gray-600">
                        <input type="hidden" name="{{ $fieldKey }}" value="0">
                        <input type="checkbox" name="{{ $fieldKey }}" value="1" class="rounded border-gray-300 text-brand-600" checked>
                        {{ $fieldLabel }}
                    </label>
                @elseif($fieldType === 'select:group_families')
                    <select name="group_family_id" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="">Familia (opcional)</option>
                        @foreach($groupFamilies as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                    </select>
                @elseif($fieldType === 'select:request_types')
                    <select name="request_type_id" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="">Tipo (opcional)</option>
                        @foreach($requestTypes as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                    </select>
                @elseif($fieldType === 'select:priorities')
                    <select name="priority_id" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="">Prioridad (opcional)</option>
                        @foreach($priorities as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                    </select>
                @elseif($fieldType === 'select:units')
                    <select name="unit" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="days">Días</option>
                        <option value="hours">Horas</option>
                    </select>
                @elseif($fieldType === 'select:applies_to')
                    <select name="applies_to" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="global">Global</option>
                        <option value="request_type">Por tipo</option>
                        <option value="priority">Por prioridad</option>
                    </select>
                @else
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500">{{ $fieldLabel }}</label>
                        <input type="{{ $fieldType }}" name="{{ $fieldKey }}" class="mt-0.5 w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm" placeholder="{{ $fieldLabel }}">
                    </div>
                @endif
            @endforeach
            <div class="flex items-end">
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Agregar</button>
            </div>
        </form>

        {{-- Tabla de registros --}}
        <div x-data="{ editId: null }">
            @forelse($items as $item)
                <div class="border-b border-gray-100">
                    <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                        <span class="w-52 truncate text-sm font-medium text-gray-800">{{ $item->name }}</span>
                        @if($item->color)<span class="inline-block h-5 w-5 rounded-full ring-1 ring-gray-200" style="background-color: {{ $item->color }}"></span>@endif
                        @if(isset($item->slug))<span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500">{{ $item->slug }}</span>@endif
                        @if(isset($item->code))<span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500">{{ $item->code }}</span>@endif
                        @if(isset($item->group_family_id))<span class="text-xs text-gray-500">{{ $item->family?->name ?? '' }}</span>@endif
                        @if(isset($item->request_type_id))<span class="text-xs text-gray-500">{{ optional($item->requestType)->name ?? '' }}</span>@endif
                        @if(isset($item->priority_id))<span class="text-xs text-gray-500">{{ optional($item->priority)->name ?? '' }}</span>@endif
                        @if(isset($item->unit))<span class="text-xs text-gray-500">{{ $item->value }} {{ $item->unit }} · {{ $item->applies_to }}</span>@endif
                        @if(array_key_exists('is_active', $item->getAttributes()))<span class="text-xs text-gray-400">{{ $item->is_active ? 'Activo' : 'Inactivo' }}</span>@endif

                        <div class="ml-auto flex shrink-0 gap-2">
                            <button type="button" @click="editId = editId === {{ $item->id }} ? null : {{ $item->id }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">Editar</button>
                            <form method="POST" action="{{ route('settings.destroy', [$activeTab, $item->id]) }}" onsubmit="return confirm('¿Eliminar este registro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Eliminar</button>
                            </form>
                        </div>
                    </div>

                    {{-- Formulario de edición inline --}}
                    <div x-show="editId === {{ $item->id }}" x-cloak class="border-t border-gray-100 bg-gray-50 p-4">
                        <form method="POST" action="{{ route('settings.update', [$activeTab, $item->id]) }}" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @csrf
                            @method('PUT')
                            @foreach($config['fields'] as $field)
                                @php [$fieldKey, $fieldLabel, $fieldType] = $field; @endphp
                                @if($fieldType === 'checkbox')
                                    <label class="flex items-center gap-2 pt-5 text-xs font-medium text-gray-600">
                                        <input type="hidden" name="{{ $fieldKey }}" value="0">
                                        <input type="checkbox" name="{{ $fieldKey }}" value="1" class="rounded border-gray-300 text-brand-600" {{ $item->{$fieldKey} ? 'checked' : '' }}>
                                        {{ $fieldLabel }}
                                    </label>
                                @elseif($fieldType === 'color')
                                    <div><label class="block text-[11px] font-medium text-gray-500">{{ $fieldLabel }}</label>
                                    <input type="color" name="{{ $fieldKey }}" value="{{ $item->{$fieldKey} }}" class="mt-0.5 w-full rounded-lg border border-gray-300 p-1"></div>
                                @elseif($fieldType === 'select:group_families')
                                    <select name="group_family_id" class="mt-5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Familia</option>
                                        @foreach($groupFamilies as $opt)<option value="{{ $opt->id }}" {{ $item->group_family_id === $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>@endforeach
                                    </select>
                                @elseif($fieldType === 'select:request_types')
                                    <select name="request_type_id" class="mt-5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Tipo</option>
                                        @foreach($requestTypes as $opt)<option value="{{ $opt->id }}" {{ $item->request_type_id === $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>@endforeach
                                    </select>
                                @elseif($fieldType === 'select:priorities')
                                    <select name="priority_id" class="mt-5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Prioridad</option>
                                        @foreach($priorities as $opt)<option value="{{ $opt->id }}" {{ $item->priority_id === $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>@endforeach
                                    </select>
                                @elseif($fieldType === 'select:units')
                                    <select name="unit" class="mt-5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                        <option value="days" {{ $item->unit === 'days' ? 'selected' : '' }}>Días</option>
                                        <option value="hours" {{ $item->unit === 'hours' ? 'selected' : '' }}>Horas</option>
                                    </select>
                                @elseif($fieldType === 'select:applies_to')
                                    <select name="applies_to" class="mt-5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                        <option value="global" {{ $item->applies_to === 'global' ? 'selected' : '' }}>Global</option>
                                        <option value="request_type" {{ $item->applies_to === 'request_type' ? 'selected' : '' }}>Por tipo</option>
                                        <option value="priority" {{ $item->applies_to === 'priority' ? 'selected' : '' }}>Por prioridad</option>
                                    </select>
                                @else
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">{{ $fieldLabel }}</label>
                                        <input type="{{ $fieldType }}" name="{{ $fieldKey }}" value="{{ $item->{$fieldKey} }}" class="mt-0.5 w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm">
                                    </div>
                                @endif
                            @endforeach
                            <div class="flex items-end">
                                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">Sin registros en este catálogo.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection