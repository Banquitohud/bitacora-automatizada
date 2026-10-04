@extends('layouts.app')

@section('page-title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('title', 'Usuarios')

@section('content')
<div class="mx-auto max-w-xl">
    <div class="mb-5">
        <a href="{{ route('users.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Volver a usuarios</a>
        <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $user->exists ? 'Editar usuario' : 'Crear usuario' }}</h2>
    </div>

    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre completo *</label>
            <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Correo electrónico *</label>
            <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ $user->exists ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}</label>
            <input type="password" name="password" {{ $user->exists ? '' : 'required' }} class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Rol *</label>
                <select name="role" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="analyst" {{ old('role', $user->role) === 'analyst' ? 'selected' : '' }}>Analista</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="is_active" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Guardar</button>
        </div>
    </form>
</div>
@endsection