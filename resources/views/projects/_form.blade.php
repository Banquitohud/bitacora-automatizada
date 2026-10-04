@php
    $project = $project ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Información del proyecto</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Nombre del proyecto *</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" name="code" value="{{ old('code', $project->code) }}" placeholder="PROJ-2026-01" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="project_status_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ old('project_status_id', $project->project_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Prioridad</label>
                <select name="priority_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}" {{ old('priority_id', $project->priority_id) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Responsable</label>
                <select name="responsible_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Sin asignar —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('responsible_id', $project->responsible_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha estimada de finalización</label>
                <input type="date" name="estimated_end_date" value="{{ old('estimated_end_date', $project->estimated_end_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha real de finalización</label>
                <input type="date" name="actual_end_date" value="{{ old('actual_end_date', $project->actual_end_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Porcentaje de avance (%)</label>
                <input type="number" name="progress" min="0" max="100" value="{{ old('progress', $project->progress) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-400">Se recalcula automáticamente desde las tareas.</p>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('description', $project->description) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                <textarea name="observations" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('observations', $project->observations) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ url()->previous() }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancelar</a>
        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">{{ $submitLabel }}</button>
    </div>
</form>