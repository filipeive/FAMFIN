@props([
    'header' => null,
])

<!-- Header (Mobile + Desktop) -->
<header class="pt-[max(1.15rem,env(safe-area-inset-top))] pb-3.5 px-4 md:py-4 md:px-8 border-b border-slate-800/80 bg-slate-900/90 backdrop-blur-xl sticky top-0 z-30 transition-all">
    <div class="flex items-center justify-between gap-3">
        <!-- Mobile Left: Brand Logo + Badge -->
        <div class="flex items-center space-x-2.5 md:hidden flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 active:scale-95 transition">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-sm shadow-md shadow-emerald-500/25">
                    F
                </div>
                <div class="flex items-center">
                    <span class="text-sm font-extrabold tracking-tight text-white">FAMFIN</span>
                    <span class="ml-1 text-[9px] font-extrabold px-1.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">PRO</span>
                </div>
            </a>
        </div>

        <!-- Desktop Left: Page Title / Breadcrumb -->
        <div class="hidden md:block">
            <h2 class="text-xl font-extrabold tracking-tight text-white flex items-center">
                {{ $header ?? 'Painel Principal' }}
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Gestão Financeira Familiar &bull; Moçambique (MZN)</p>
        </div>

        <!-- Mobile Center / Header Title -->
        <div class="md:hidden text-center flex-1 truncate px-1">
            <span class="text-xs font-bold text-slate-200 truncate block">
                {{ $header ?? 'Painel Principal' }}
            </span>
        </div>

        <!-- Right: Actions & 3-Dots Menu -->
        <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
            <!-- New Transaction Button (DESKTOP ONLY - On Mobile the '+' is prominent on Bottom-Nav) -->
            <a href="{{ route('transactions.create') }}" 
               class="hidden md:inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition shadow-lg shadow-emerald-500/20 active:scale-95 transform">
                <x-icon name="plus" class="w-4 h-4 mr-1.5 stroke-[2.5]" />
                <span>Nova Transação</span>
            </a>

            <!-- Profile Quick Link (Desktop Only) -->
            <a href="{{ route('profile.edit') }}" title="Editar Perfil" class="hidden sm:inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700/80 border border-slate-700/70 text-slate-200 transition shadow-sm">
                <x-icon name="user" class="w-4 h-4 mr-1.5 text-emerald-400" />
                Perfil
            </a>

            <!-- Logout Button (Desktop Only) -->
            <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form" class="hidden md:inline-flex">
                @csrf
                <button type="button" 
                        onclick="confirmLogout('topbar-logout-form')"
                        title="Terminar Sessão"
                        class="inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-rose-500/15 border border-slate-700/70 hover:border-rose-500/30 text-slate-300 hover:text-rose-400 transition group shadow-sm active:scale-95">
                    <x-icon name="logout" class="w-4 h-4 mr-1.5 text-slate-400 group-hover:text-rose-400 transition" />
                    <span>Sair</span>
                </button>
            </form>

            <!-- 3-Dots Menu Button (Mobile & Desktop) -->
            <div class="relative">
                <button type="button" 
                        id="topbarMoreMenuBtn"
                        onclick="toggleTopbarMoreMenu(event)"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700/70 text-slate-300 hover:text-white flex items-center justify-center transition active:scale-90 shadow-sm"
                        title="Menu de opções rápidas"
                        aria-label="Mais opções">
                    <x-icon name="dots-vertical" class="w-5 h-5" />
                </button>

                <!-- 3-Dots Dropdown Menu -->
                <div id="topbarMoreMenuDropdown" 
                     class="hidden absolute right-0 mt-2.5 w-64 rounded-2xl bg-slate-900/95 backdrop-blur-2xl border border-slate-800 shadow-[0_15px_40px_rgba(0,0,0,0.65)] p-2 z-50 transition-all animate-in fade-in zoom-in-95 duration-150">
                    
                    <!-- User Preview -->
                    <div class="p-2.5 rounded-xl bg-slate-800/60 border border-slate-700/50 mb-2 flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'FS', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()?->name ?? 'Filipe dos Santos' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()?->email ?? 'filipe@famfin.app' }}</p>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="space-y-0.5">
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="user" class="w-4 h-4 mr-2.5 text-emerald-400" />
                            Meu Perfil
                        </a>

                        <a href="{{ route('accounts.index') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="account" class="w-4 h-4 mr-2.5 text-sky-400" />
                            Contas Bancárias
                        </a>

                        <a href="{{ route('rules.index') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="rules" class="w-4 h-4 mr-2.5 text-purple-400" />
                            Regras & Deduções
                        </a>

                        <a href="{{ route('savings.index') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="savings" class="w-4 h-4 mr-2.5 text-emerald-400" />
                            Poupança & Metas
                        </a>

                        <a href="{{ route('debts.index') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="debt" class="w-4 h-4 mr-2.5 text-rose-400" />
                            Dívidas & Créditos
                        </a>

                        <a href="{{ route('reports.index') }}" 
                           class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            <x-icon name="reports" class="w-4 h-4 mr-2.5 text-amber-400" />
                            Relatórios & Analytics
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-800/80 my-1.5"></div>

                    <!-- Logout Option -->
                    <form method="POST" action="{{ route('logout') }}" id="dropdown-logout-form">
                        @csrf
                        <button type="button" 
                                onclick="confirmLogout('dropdown-logout-form')"
                                class="w-full flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/15 transition">
                            <x-icon name="logout" class="w-4 h-4 mr-2.5 text-rose-400" />
                            Terminar Sessão
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    window.toggleTopbarMoreMenu = function(e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('topbarMoreMenuDropdown');
        if (!dropdown) return;
        dropdown.classList.toggle('hidden');
    };

    // Close 3-dots dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('topbarMoreMenuDropdown');
        const btn = document.getElementById('topbarMoreMenuBtn');
        if (!dropdown || dropdown.classList.contains('hidden')) return;
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
