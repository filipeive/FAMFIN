@props([
    'isMobile' => false,
])

<div class="h-full flex flex-col justify-between bg-slate-900/95 border-r border-slate-800/80 backdrop-blur-md">
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

        @if($isMobile)
            <button type="button" onclick="toggleMobileDrawer()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        @endif
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

        <!-- Patrimônio & Contas -->
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

        <!-- Inteligência & Relatórios -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Inteligência & Relatórios</div>
            <div class="space-y-1">
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('reports.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <x-icon name="reports" class="w-4 h-4 mr-2.5 {{ request()->routeIs('reports.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                    Relatórios & Analytics
                </a>

                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all {{ request()->routeIs('profile.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <x-icon name="user" class="w-4 h-4 mr-2.5 {{ request()->routeIs('profile.*') ? 'text-emerald-400' : 'text-slate-400' }}" />
                    Meu Perfil
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

            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form-{{ $isMobile ? 'mobile' : 'desktop' }}" class="ml-1.5 flex-shrink-0">
                @csrf
                <button type="button" onclick="confirmLogout('sidebar-logout-form-{{ $isMobile ? 'mobile' : 'desktop' }}')" title="Terminar Sessão" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/15 transition">
                    <x-icon name="logout" class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>
</div>

