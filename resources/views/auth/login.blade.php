<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — {{ config('gsi.app_label') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-brand-900 px-4 py-12 font-sans">

    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl font-bold text-white shadow-lg">G</span>
            <h1 class="mt-4 text-2xl font-bold text-white">Bitácora GSI</h1>
            <p class="text-sm text-gray-300">Gestión de accesos y seguimiento del flujo diario</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl">
            <h2 class="text-lg font-semibold text-gray-900">Iniciar sesión</h2>
            <p class="mt-1 text-sm text-gray-500">Ingresa con tu cuenta del equipo GSI.</p>

            @if($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" autofocus
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Recordarme
                </label>

                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Ingresar
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            Uso interno · Si no recuerdas tu contraseña, contacta al administrador del sistema.
        </p>
    </div>

</body>
</html>
