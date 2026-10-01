@php
    $case = $case ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    {{-- Datos básicos --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Datos básicos</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Número de caso / ticket</label>
                <input type="text" name="case_number" value="{{ old('case_number', $case->case_number) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha de recepción *</label>
                <input type="date" name="received_date" value="{{ old('received_date', $case->received_date?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hora de recepción</label>
                <input type="time" name="received_time" value="{{ old('received_time', $case->received_time?->format('H:i')) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha límite</label>
                <input type="date" name="due_date" value="{{ old('due_date', $case->due_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Solicitante</label>
                <input type="text" name="requester" value="{{ old('requester', $case->requester) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Usuario afectado</label>
                <input type="text" name="affected_user" value="{{ old('affected_user', $case->affected_user) }}" placeholder="usuario@empresa.com" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cargo</label>
                <input type="text" name="position" value="{{ old('position', $case->position) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Proyecto(s) asociado(s)</label>
                <select name="project_ids[]" multiple class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $case->projects->contains($project->id) ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Solicitud --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Solicitud</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo de solicitud</label>
                <select name="request_type_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($requestTypes as $type)
                        <option value="{{ $type->id }}" {{ old('request_type_id', $case->request_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Aplicación</label>
                <select name="application_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($applications as $app)
                        <option value="{{ $app->id }}" {{ old('application_id', $case->application_id) == $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Perfil solicitado</label>
                <select name="profile_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" {{ old('profile_id', $case->profile_id) == $profile->id ? 'selected' : '' }}>{{ $profile->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Permiso / transacción</label>
                <input type="text" name="permission" value="{{ old('permission', $case->permission) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Grupo</label>
                <select name="group_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id', $case->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Familia de grupo</label>
                <select name="group_family_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Seleccionar —</option>
                    @foreach($groupFamilies as $family)
                        <option value="{{ $family->id }}" {{ old('group_family_id', $case->group_family_id) == $family->id ? 'selected' : '' }}>{{ $family->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
