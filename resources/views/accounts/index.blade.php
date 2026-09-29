@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                🏦 Contas & Carteiras
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Gestão de contas bancárias, contas móveis (M-Pesa, e-Mola) e numerário</p>
        </div>
    </div>

    <!-- Main Grid: Accounts List + Create Account Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Accounts Cards (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <h3 class="text-base font-bold text-white">Contas da Família</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($accounts as $account)
                    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center text-xl">
                                        {{ $account->icon ?? '🏦' }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">{{ $account->name }}</h4>
                                        <span class="text-[10px] text-slate-400 uppercase font-semibold">{{ $account->type->label() }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $account->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $account->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>

                            <div class="mt-4">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold">Saldo Actual</span>
                                <p class="text-2xl font-black text-white mt-0.5">{{ $account->formatted_balance }}</p>
                                <span class="text-[11px] text-slate-500">Saldo Inicial: {{ formatMoney($account->initial_balance, $family->currency) }}</span>
                            </div>
                        </div>

                        <!-- Recalculate Balance Button -->
                        <form method="POST" action="{{ route('accounts.recalculate', $account->id) }}" class="pt-3 border-t border-slate-800 flex justify-between items-center text-xs">
                            @csrf
                            <span class="text-[11px] text-slate-400">{{ $account->transactions()->count() }} transacções</span>
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition text-[11px]">
                                🔄 Recalcular Livro-Razão
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- New Account Form (4 cols) -->
        <div class="lg:col-span-4">
            <form method="POST" action="{{ route('accounts.store') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Adicionar Nova Conta</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nome da Conta</label>
                    <input type="text" name="name" required placeholder="Ex: Conta BCI, M-Pesa, Carteira" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Tipo de Conta</label>
                    <select name="type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                        <option value="checking">Conta Corrente (Ordem)</option>
                        <option value="savings">Conta Poupança</option>
                        <option value="mobile_money">Mobile Money (M-Pesa / e-Mola / mKesh)</option>
                        <option value="cash">Numerário / Carteira Física</option>
                        <option value="credit_card">Cartão de Crédito</option>
                        <option value="investment">Investimento / Aplicação</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Saldo Inicial (MZN)</label>
                    <input type="text" name="initial_balance" required value="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Ícone</label>
                    <input type="text" name="icon" value="🏦" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="include_in_total" name="include_in_total" value="1" checked class="rounded border-slate-800 bg-slate-950 text-emerald-500 focus:ring-0">
                    <label for="include_in_total" class="text-xs text-slate-300">Incluir no saldo patrimonial total</label>
                </div>

                <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                    Criar Conta
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
