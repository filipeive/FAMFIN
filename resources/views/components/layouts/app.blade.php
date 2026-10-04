<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'FAMFIN' }} — Gestão Financeira Familiar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SweetAlert2 Dark Themed -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            background: #0f172a !important;
            border: 1px solid #1e293b !important;
            border-radius: 1.25rem !important;
            color: #f8fafc !important;
            font-family: inherit !important;
        }
        .swal2-title {
            color: #ffffff !important;
            font-size: 1.15rem !important;
            font-weight: 700 !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 0.875rem !important;
        }
        .swal2-confirm {
            background-color: #ef4444 !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
        }
        .swal2-cancel {
            background-color: #334155 !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
            color: #cbd5e1 !important;
        }
        .swal2-toast {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5) !important;
        }
    </style>
</head>
<body class="h-full antialiased font-sans flex flex-col md:flex-row bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-slate-950">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-slate-900/95 border-r border-slate-800/80 flex flex-col flex-shrink-0 backdrop-blur-md">
        <!-- Brand Header -->
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 shadow-lg shadow-emerald-500/25 font-black text-xl tracking-wider group-hover:scale-105 transition transform">
                    F
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white flex items-center">
                        FAMFIN
                        <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">PRO</span>
                    </h1>
                    <p class="text-[11px] font-medium text-slate-400">Moçambique &bull; MZN</p>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto custom-scrollbar">
            <!-- Principal -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Principal</div>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="dashboard" class="w-4 h-4 mr-2.5 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Painel Geral
                    </a>

                    <a href="{{ route('transactions.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('transactions.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="transactions" class="w-4 h-4 mr-2.5 {{ request()->routeIs('transactions.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Transações
                    </a>

                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('categories.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="tag" class="w-4 h-4 mr-2.5 {{ request()->routeIs('categories.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Categorias
                    </a>

                    <a href="{{ route('budgets.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('budgets.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="budget" class="w-4 h-4 mr-2.5 {{ request()->routeIs('budgets.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Orçamentos
                    </a>
                </div>
            </div>

            <!-- Planejamento & Finanças -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Patrimônio & Contas</div>
                <div class="space-y-1">
                    <a href="{{ route('accounts.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('accounts.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="account" class="w-4 h-4 mr-2.5 {{ request()->routeIs('accounts.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Contas Bancárias
                    </a>

                    <a href="{{ route('savings.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('savings.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="savings" class="w-4 h-4 mr-2.5 {{ request()->routeIs('savings.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Poupança & Metas
                    </a>

                    <a href="{{ route('debts.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('debts.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="debt" class="w-4 h-4 mr-2.5 {{ request()->routeIs('debts.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Dívidas & Créditos
                    </a>

                    <a href="{{ route('assets.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('assets.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="scale" class="w-4 h-4 mr-2.5 {{ request()->routeIs('assets.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Patrimônio & Ativos
                    </a>

                    <a href="{{ route('rules.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('rules.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="rules" class="w-4 h-4 mr-2.5 {{ request()->routeIs('rules.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Regras & Deduções
                    </a>
                </div>
            </div>

            <!-- Análise & Configuração -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Inteligência & Relatórios</div>
                <div class="space-y-1">
                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('reports.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                        <x-icon name="reports" class="w-4 h-4 mr-2.5 {{ request()->routeIs('reports.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                        Relatórios & Analytics
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Profile & Sidebar Logout -->
        <div class="p-3 border-t border-slate-800/80 bg-slate-950/40">
            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/50 border border-slate-700/50">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2.5 min-w-0 flex-1 hover:opacity-90 transition">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'FS', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-200 truncate">{{ auth()->user()?->name ?? 'Filipe dos Santos' }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()?->email ?? 'filipe@famfin.app' }}</p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form" class="ml-1.5 flex-shrink-0">
                    @csrf
                    <button type="button" onclick="confirmLogout('sidebar-logout-form')" title="Terminar Sessão" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/15 transition">
                        <x-icon name="logout" class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-950">
        <!-- Topbar Navbar (Clean, with generous breathing room and SweetAlert logout) -->
        <header class="py-4 px-6 md:px-8 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md sticky top-0 z-30 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Page Title / Breadcrumb -->
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-white flex items-center">
                        {{ $header ?? 'Painel Principal' }}
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Gestão Financeira Integrada &bull; Família dos Santos</p>
                </div>

                <!-- Topbar Actions & Prominent Logout Button -->
                <div class="flex items-center space-x-3">
                    <!-- New Transaction Quick Action -->
                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transform">
                        <x-icon name="plus" class="w-4 h-4 mr-1.5 stroke-[2.5]" />
                        Nova Transação
                    </a>

                    <!-- Profile Quick Link -->
                    <a href="{{ route('profile.edit') }}" title="Editar Perfil" class="hidden sm:inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700/80 border border-slate-700/70 text-slate-200 transition shadow-sm">
                        <x-icon name="user" class="w-4 h-4 mr-1.5 text-emerald-400" />
                        Perfil
                    </a>

                    <!-- Prominent Logout Button in Navbar -->
                    <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form" class="inline-flex">
                        @csrf
                        <button type="button" 
                                onclick="confirmLogout('topbar-logout-form')"
                                class="inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-rose-500/15 border border-slate-700/70 hover:border-rose-500/30 text-slate-300 hover:text-rose-400 transition group shadow-sm">
                            <x-icon name="logout" class="w-4 h-4 mr-1.5 text-slate-400 group-hover:text-rose-400 transition" />
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="w-full flex-1 p-6 md:p-8 space-y-8">
            {{ $slot }}
        </main>
    </div>

    <!-- SweetAlert2 Notifications & Helper Scripts -->
    <script>
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Flash message toasts
        @if(session('success'))
            window.Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                iconColor: '#10b981'
            });
        @endif

        @if(session('error'))
            window.Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                iconColor: '#f43f5e'
            });
        @endif

        @if(session('info'))
            window.Toast.fire({
                icon: 'info',
                title: '{{ session('info') }}',
                iconColor: '#38bdf8'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Atenção aos seguintes erros:',
                html: '<div class="text-left text-xs space-y-1 mt-2">@foreach($errors->all() as $error)<p>&bull; {{ addslashes($error) }}</p>@endforeach</div>',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#10b981',
            });
        @endif

        // Global confirmation helpers
        window.confirmAction = function(formIdOrElement, title = 'Tem a certeza?', text = 'Esta ação não poderá ser revertida!', confirmBtn = 'Sim, continuar') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Cancelar',
                iconColor: '#f59e0b',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof formIdOrElement === 'string') {
                        document.getElementById(formIdOrElement).submit();
                    } else if (formIdOrElement instanceof HTMLFormElement) {
                        formIdOrElement.submit();
                    } else if (typeof formIdOrElement === 'function') {
                        formIdOrElement();
                    }
                }
            });
        };

        window.confirmLogout = function(formId = 'topbar-logout-form') {
            Swal.fire({
                title: 'Terminar Sessão',
                text: 'Deseja realmente sair da sua conta FAMFIN?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Sim, Sair',
                cancelButtonText: 'Ficar',
                iconColor: '#38bdf8',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        };
    </script>
</body>
</html>
