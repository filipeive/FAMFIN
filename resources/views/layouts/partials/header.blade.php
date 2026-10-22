@props([
    'header' => null,
])

<!-- Header (Mobile + Desktop) -->
<header class="py-3 px-4 md:py-4 md:px-8 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md sticky top-0 z-30 transition-all">
    <div class="flex items-center justify-between gap-4">
        <!-- Mobile Left: Hamburger Button + Logo -->
        <div class="flex items-center space-x-3 md:hidden">
            <button type="button" 
                    onclick="toggleMobileDrawer()" 
                    class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 active:scale-95 transition"
                    aria-label="Abrir Menu">
                <x-icon name="menu" class="w-6 h-6" />
            </button>

            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-sm shadow-md shadow-emerald-500/20">
                    F
                </div>
                <span class="text-sm font-extrabold tracking-tight text-white">FAMFIN</span>
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

        <!-- Right: Actions -->
        <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- New Transaction Quick Action -->
            <a href="{{ route('transactions.create') }}" 
               class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2.5 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition shadow-lg shadow-emerald-500/20 active:scale-95 transform">
                <x-icon name="plus" class="w-4 h-4 md:mr-1.5 stroke-[2.5]" />
                <span class="hidden md:inline">Nova Transação</span>
            </a>

            <!-- Profile Quick Link (Desktop) -->
            <a href="{{ route('profile.edit') }}" title="Editar Perfil" class="hidden sm:inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700/80 border border-slate-700/70 text-slate-200 transition shadow-sm">
                <x-icon name="user" class="w-4 h-4 mr-1.5 text-emerald-400" />
                Perfil
            </a>

            <!-- Logout Button in Navbar -->
            <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form" class="inline-flex">
                @csrf
                <button type="button" 
                        onclick="confirmLogout('topbar-logout-form')"
                        title="Terminar Sessão"
                        class="inline-flex items-center p-2 md:px-3.5 md:py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-rose-500/15 border border-slate-700/70 hover:border-rose-500/30 text-slate-300 hover:text-rose-400 transition group shadow-sm active:scale-95">
                    <x-icon name="logout" class="w-4 h-4 md:mr-1.5 text-slate-400 group-hover:text-rose-400 transition" />
                    <span class="hidden md:inline">Sair</span>
                </button>
            </form>
        </div>
    </div>
</header>
