<!-- Mobile Bottom Navigation Bar (md:hidden) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-xl border-t border-slate-800 shadow-[0_-4px_20px_rgba(0,0,0,0.5)] pb-[max(0.4rem,env(safe-area-inset-bottom))] pt-1.5 transition-all">
    <div class="flex items-center justify-around h-12 px-2 max-w-lg mx-auto">
        <!-- Início / Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-bold tracking-tight transition-all active:scale-90 {{ request()->routeIs('dashboard*') ? 'text-emerald-400 font-extrabold' : 'text-slate-400 hover:text-slate-200' }}">
            <div class="relative flex items-center justify-center">
                <x-icon name="dashboard" class="w-5 h-5 {{ request()->routeIs('dashboard*') ? 'text-emerald-400 stroke-[2.5]' : 'text-slate-400' }}" />
            </div>
            <span class="text-[9px] mt-0.5">Início</span>
        </a>

        <!-- Transações -->
        <a href="{{ route('transactions.index') }}" 
           class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-bold tracking-tight transition-all active:scale-90 {{ request()->routeIs('transactions.index*') || request()->routeIs('transactions.edit*') ? 'text-emerald-400 font-extrabold' : 'text-slate-400 hover:text-slate-200' }}">
            <div class="relative flex items-center justify-center">
                <x-icon name="transactions" class="w-5 h-5 {{ request()->routeIs('transactions.index*') || request()->routeIs('transactions.edit*') ? 'text-emerald-400 stroke-[2.5]' : 'text-slate-400' }}" />
            </div>
            <span class="text-[9px] mt-0.5">Transações</span>
        </a>

        <!-- Botão Central: Nova Transação (+) -->
        <div class="flex flex-col items-center justify-center flex-1 py-0 relative">
            <a href="{{ route('transactions.create') }}" 
               title="Nova Transação"
               class="w-12 h-12 -mt-5 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center shadow-lg shadow-emerald-500/35 border-4 border-slate-950 active:scale-95 hover:scale-105 transition-transform group">
                <x-icon name="plus" class="w-6 h-6 stroke-[3] group-hover:rotate-90 transition-transform duration-300" />
            </a>
            <span class="text-[9px] font-bold text-emerald-400 mt-0.5">Nova</span>
        </div>

        <!-- Orçamentos -->
        <a href="{{ route('budgets.index') }}" 
           class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-bold tracking-tight transition-all active:scale-90 {{ request()->routeIs('budgets*') ? 'text-emerald-400 font-extrabold' : 'text-slate-400 hover:text-slate-200' }}">
            <div class="relative flex items-center justify-center">
                <x-icon name="budget" class="w-5 h-5 {{ request()->routeIs('budgets*') ? 'text-emerald-400 stroke-[2.5]' : 'text-slate-400' }}" />
            </div>
            <span class="text-[9px] mt-0.5">Orçamentos</span>
        </a>

        <!-- Menu Completo / Gaveta -->
        <button type="button" 
                onclick="toggleMobileDrawer()" 
                class="flex flex-col items-center justify-center flex-1 py-1 text-[10px] font-bold tracking-tight transition-all active:scale-90 text-slate-400 hover:text-slate-200">
            <div class="relative flex items-center justify-center">
                <x-icon name="menu" class="w-5 h-5 text-slate-400" />
            </div>
            <span class="text-[9px] mt-0.5">Mais</span>
        </button>
    </div>
</nav>
