<!DOCTYPE html>
<html lang="pt-MZ" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'FAMFIN' }} — Gestão Financeira Familiar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-950 text-white font-bold text-lg">
                            💎
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-white">FAM<span class="text-emerald-400">FIN</span></span>
                            <span class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400">Finanças Familiares</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        📊 Painel
                    </a>
                    <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        💸 Transacções
                    </a>
                    <a href="{{ route('budgets.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('budgets.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        🎯 Orçamentos
                    </a>
                    <a href="{{ route('savings.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('savings.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        💰 Poupança
                    </a>
                    <a href="{{ route('debts.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('debts.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        💳 Dívidas
                    </a>
                    <a href="{{ route('accounts.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('accounts.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        🏦 Contas
                    </a>
                    <a href="{{ route('rules.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('rules.*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        ⚙️ Regras (INSS/Dízimo)
                    </a>
                </nav>

                <!-- Family & User Profile Badge -->
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-950/80 text-emerald-400 border border-emerald-800/50">
                        🇲🇿 MZN
                    </span>
                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-sm transition">
                        + Registar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-900/40 border border-emerald-500/40 text-emerald-200 text-sm flex items-center justify-between shadow-lg">
                <span>✅ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-900/40 border border-rose-500/40 text-rose-200 text-sm flex items-center justify-between shadow-lg">
                <span>⚠️ {{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-900/40 border border-rose-500/40 text-rose-200 text-sm shadow-lg">
                <p class="font-semibold mb-1">Por favor corrija os seguintes erros:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 py-6 text-center text-xs text-slate-400">
        <p>FAMFIN — Sistema Familiar de Gestão Financeira • Construído com carinho para a Família dos Santos 🇲🇿</p>
    </footer>

</body>
</html>
