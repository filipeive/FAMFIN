<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#020617">
    <title>Criar Conta — FAMFIN</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans flex items-center justify-center p-4 bg-slate-950">
    <div class="max-w-md w-full my-8">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 shadow-xl shadow-emerald-500/20 font-black text-2xl tracking-wider mb-3">
                F
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">FAMFIN</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Crie a sua conta de gestão familiar</p>
        </div>

        <!-- Register Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white">Criar Nova Conta</h2>
                <p class="text-xs text-slate-400 mt-0.5">Preencha os dados abaixo para começar</p>
            </div>

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center">
                            <x-icon name="alert-triangle" class="w-4 h-4 mr-2 flex-shrink-0" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome Completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Filipe dos Santos" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Família / Agregado</label>
                    <input type="text" name="family_name" value="{{ old('family_name') }}" placeholder="ex: Família Filipe dos Santos" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Endereço de E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="seu.email@exemplo.com" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Palavra-passe</label>
                        <input type="password" name="password" required placeholder="Mínimo 8 caracteres" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Confirmar Palavra-passe</label>
                        <input type="password" name="password_confirmation" required placeholder="Repetir palavra-passe" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20 mt-3">
                    Criar Conta Familiar
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Já possui uma conta?
                <a href="{{ route('login') }}" class="font-bold text-emerald-400 hover:text-emerald-300 ml-1">Iniciar sessão</a>
            </div>
        </div>
    </div>
</body>
</html>
