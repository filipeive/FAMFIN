<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#020617">
    <title>Iniciar Sessão — FAMFIN</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans flex items-center justify-center p-4 bg-slate-950">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center text-slate-950 shadow-xl shadow-emerald-500/20 font-black text-2xl tracking-wider mb-4">
                F
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">FAMFIN</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Gestão Financeira Familiar &bull; Moçambique</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-white">Iniciar Sessão</h2>
                <p class="text-xs text-slate-400 mt-0.5">Aceda ao seu painel de finanças familiares</p>
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

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Endereço de E-mail</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="seu.email@exemplo.com" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl pl-4 pr-10 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                            <x-icon name="user" class="w-4 h-4" />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Palavra-passe</label>
                    <div class="relative">
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl pl-4 pr-10 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                            <x-icon name="lock" class="w-4 h-4" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                        <span class="ml-2">Lembrar-me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20 mt-2">
                    Entrar na Conta
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Ainda não tem conta?
                <a href="{{ route('register') }}" class="font-bold text-emerald-400 hover:text-emerald-300 ml-1">Criar conta</a>
            </div>
        </div>
    </div>
</body>
</html>
