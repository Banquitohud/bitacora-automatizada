@extends('layouts.app')

@section('page-title', 'Usuarios')
@section('title', 'Usuarios')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Usuarios del sistema</h2>
            <p class="text-sm text-gray-500">Administración de cuentas y roles del equipo GSI.</p>
        </div>
        <a href="{{ route('users.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">+ Nuevo usuario</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Usuario</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Rol</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Casos asignados</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                <x-badge :color="$user->isAdmin() ? 'dark' : 'blue'">{{ $user->isAdmin() ? 'Administrador' : 'Analista' }}</x-badge>
                            </td>
                            <td class="px-5 py-3">
                                @if($user->is_active)
                                    <x-badge color="green">Activo</x-badge>
                                @else
                                    <x-badge color="red">Inactivo</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $user->assignedCases()->count() }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                <a href="{{ route('users.edit', $user) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">Editar</a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection