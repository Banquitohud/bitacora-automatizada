@extends('layouts.app')

@section('page-title', 'Notificaciones')
@section('title', 'Notificaciones')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Notificaciones</h2>
            <p class="text-sm text-gray-500">Alertas internas del sistema.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Marcar todas como leídas</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm {{ $notification->isRead() ? '' : 'border-l-4 border-l-brand-500' }}">
                <span class="mt-1 h-3 w-3 shrink-0 rounded-full {{ match($notification->type) { 'danger' => 'bg-red-500', 'warning' => 'bg-amber-500', 'success' => 'bg-emerald-500', default => 'bg-blue-500' } }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ $notification->title }}</p>
                    @if($notification->body)<p class="mt-0.5 text-sm text-gray-500">{{ $notification->body }}</p>@endif
                    <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if($notification->link)
                        <a href="{{ $notification->link }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">Ir</a>
                    @endif
                    @if(! $notification->isRead())
                        <form method="POST" action="{{ route('notifications.read.one', $notification) }}">
                            @csrf
                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50">Leída</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('¿Eliminar esta notificación?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-400">
                No tienes notificaciones.
            </div>
        @endforelse
    </div>

    <div>
        {{ $notifications->links() }}
    </div>
</div>
@endsection