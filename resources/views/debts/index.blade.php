@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                💳 Gestão de Dívidas & Empréstimos
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Controlo rigoroso de valores a pagar e valores a receber</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Total Original Registado</span>
            <p class="text-2xl font-black text-white mt-1">{{ $summary['formatted_total_original'] }}</p>
        </div>
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Saldo Devedor Restante</span>
            <p class="text-2xl font-black text-rose-400 mt-1">{{ $summary['formatted_total_remaining'] }}</p>
            <span class="text-[10px] text-slate-400">{{ $summary['active_debts_count'] }} dívida(s) activas</span>
        </div>
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Total Já Amortizado</span>
            <p class="text-2xl font-black text-emerald-400 mt-1">{{ $summary['formatted_total_paid'] }}</p>
            <span class="text-[10px] text-slate-400">{{ $summary['progress_percentage'] }}% liquidado</span>
        </div>
    </div>

    <!-- Main Grid: Debts List + Add Debt Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Debts List (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <h3 class="text-base font-bold text-white">Dívidas Registadas</h3>

            @if($debts->isEmpty())
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-12 text-center text-slate-400 text-xs">
                    Nenhuma dívida registada. Registe compromissos financeiros ao lado.
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach($debts as $debt)
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-3 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-md font-semibold {{ $debt->type->value === 'borrowed' ? 'bg-rose-950/60 text-rose-300 border border-rose-800/40' : 'bg-emerald-950/60 text-emerald-300 border border-emerald-800/40' }}">
                                            {{ $debt->type->label() }}
                                        </span>
                                        <h4 class="font-bold text-white text-sm">{{ $debt->person_name }}</h4>
                                    </div>
                                    @if($debt->description)
                                        <p class="text-xs text-slate-400 mt-1">{{ $debt->description }}</p>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <span class="text-base font-black {{ $debt->status->value === 'paid' ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ formatMoney($debt->remaining_amount, $family->currency) }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400">de {{ formatMoney($debt->original_amount, $family->currency) }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $debt->progress_percentage }}%"></div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Amortizado: {{ formatMoney($debt->total_paid, $family->currency) }} ({{ $debt->progress_percentage }}%)</span>
                                <span>Status: <strong class="{{ $debt->status->value === 'paid' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $debt->status->label() }}</strong></span>
                            </div>

                            @if($debt->status->value !== 'paid')
                                <!-- Quick Amortize Form -->
                                <form method="POST" action="{{ route('debts.pay', $debt->id) }}" class="pt-3 border-t border-slate-800 flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="amount" required placeholder="Valor a amortizar" class="flex-1 bg-slate-950 border border-slate-800 rounded-lg p-1.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                                    <select name="account_id" class="bg-slate-950 border border-slate-800 rounded-lg p-1.5 text-[11px] text-slate-300 focus:outline-none">
                                        <option value="">Sem debitar conta</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">Debitar: {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-500 hover:bg-rose-400 text-white transition">
                                        Amortizar
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- New Debt Form (4 cols) -->
        <div class="lg:col-span-4">
            <form method="POST" action="{{ route('debts.store') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Registar Nova Dívida</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Tipo</label>
                    <select name="type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                        <option value="borrowed">Pedi emprestado (a pagar)</option>
                        <option value="lent">Emprestei (a receber)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Pessoa / Credor / Devedor</label>
                    <input type="text" name="person_name" required placeholder="Ex: Laurinda, Nico, Banco BCI" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Valor Total (MZN)</label>
                    <input type="text" name="original_amount" required placeholder="Ex: 5000" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Data de Vencimento (Opcional)</label>
                    <input type="date" name="due_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Descrição</label>
                    <textarea name="description" rows="2" placeholder="Motivo ou detalhes do acordo..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                    Registar Dívida
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
