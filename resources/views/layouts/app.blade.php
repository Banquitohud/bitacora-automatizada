<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('gsi.app_label')) — {{ config('gsi.app_label') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-gray-800 antialiased" x-data="{ sidebarOpen: true, notifOpen: false }">

    <div class="min-h-full lg:flex">

        <!-- ===== Sidebar ===== -->
        <aside class="fixed inset-y-0 left-0 z-40 w-64 transform bg-gray-900 text-gray-200 transition-transform duration-200 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div class="flex h-16 items-center justify-between border-b border-gray-800 px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white font-bold">G</span>
                    <div>
                        <p class="text-sm font-semibold leading-tight text-white">Bitácora GSI</p>
                        <p class="text-[11px] text-gray-400">Gestión de accesos</p>
                    </div>
                </a>
            </div>

            <nav class="mt-4 space-y-1 px-3 text-sm">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">Dashboard</x-nav-link>
                <x-nav-link href="{{ route('daily-cases.index') }}" :active="request()->routeIs('daily-cases.*')" icon="inbox">Flujo Diario</x-nav-link>
                <x-nav-link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')" icon="folder">Proyectos</x-nav-link>
                <x-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="chart">Reportes</x-nav-link>
                <x-nav-link href="{{ route('calendar.index') }}" :active="request()->routeIs('calendar.*')" icon="calendar">Calendario</x-nav-link>
                <x-nav-link href="{{ route('import.index') }}" :active="request()->routeIs('import.*')" icon="upload">Importar casos</x-nav-link>

                @if(auth()->user()->isAdmin())
                    <div class="pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500">Administración</div>
                    <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')" icon="users">Usuarios</x-nav-link>
                    <x-nav-link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')" icon="cog">Configuración</x-nav-link>
                @endif
            </nav>

            <div class="absolute bottom-0 left-0 right-0 border-t border-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Analista' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión" class="text-gray-400 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ===== Contenido ===== -->
        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">

            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', config('gsi.app_label'))</h1>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Notificaciones -->
                    <div class="relative" @click.outside="notifOpen = false">
                        <button @click="notifOpen = !notifOpen" class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="{{ \App\Models\Notification::query()->forUser(auth()->id())->unread()->count() > 0 }}" class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ \App\Models\Notification::query()->forUser(auth()->id())->unread()->count() }}
                            </span>
                        </button>

                        <div x-cloak x-show="notifOpen" class="absolute right-0 mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-lg">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                <p class="text-sm font-semibold">Notificaciones</p>
                                <form method="POST" action="{{ route('notifications.read') }}">
                                    @csrf
                                    <button class="text-xs text-brand-600 hover:underline">Marcar todas leídas</button>
                                </form>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(\App\Models\Notification::query()->forUser(auth()->id())->latest()->limit(8)->get() as $notification)
                                    <a href="{{ $notification->link ?: route('notifications.index') }}"
                                       class="flex gap-3 border-b border-gray-50 px-4 py-3 hover:bg-gray-50 {{ $notification->isRead() ? '' : 'bg-brand-50' }}">
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ match($notification->type) { 'danger' => 'bg-red-500', 'warning' => 'bg-amber-500', 'success' => 'bg-emerald-500', default => 'bg-blue-500' } }}"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800">{{ $notification->title }}</p>
                                            @if($notification->body)<p class="text-xs text-gray-500">{{ $notification->body }}</p>@endif
                                            <p class="mt-0.5 text-[11px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-center text-sm text-gray-400">No tienes notificaciones.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-2 text-center text-xs font-medium text-brand-600 hover:bg-gray-50">Ver todas</a>
                        </div>
                    </div>

                    <div class="hidden items-center gap-2 rounded-lg bg-gray-100 px-3 py-1.5 sm:flex">
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            @if(session('success'))
                <div class="mx-4 mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:mx-6" x-data="{show:true}" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    {{ session('success') }}
                    <button @click="show = false" class="float-right font-bold">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <main class="flex-1 px-4 py-6 sm:px-6">
                @yield('content')
            </main>

            <footer class="border-t border-gray-200 px-6 py-4 text-center text-xs text-gray-400">
                {{ config('gsi.app_label') }} · Uso interno del área GSI
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>