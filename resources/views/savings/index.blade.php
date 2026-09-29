@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                💰 Metas de Poupança & Reservas
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Construção de património, fundo de emergência e objectivos familiares</p>
        </div>
    </div>

    <!-- Goals Grid + New Goal Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Goals Cards (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <h3 class="text-base font-bold text-white">Metas Activas</h3>

            @if($goalsWithProgress->isEmpty())
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-12 text-center text-slate-400 text-xs">
                    Nenhuma meta de poupança registada. Crie o seu fundo de emergência ou primeiro objectivo ao lado.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($goalsWithProgress as $goal)
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ $goal['icon'] ?? '💰' }}</span>
                                        <div>
                                            <h4 class="font-bold text-white text-sm">{{ $goal['name'] }}</h4>
                                            @if(!empty($goal['description']))
                                                <p class="text-[11px] text-slate-400">{{ $goal['description'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($goal['is_completed'])
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800">
                                            🎉 Atingida!
                                        </span>
                                    @endif
                                </div>

                                <!-- Progress & Numbers -->
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-baseline justify-between text-xs">
                                        <span class="text-lg font-black text-emerald-400">{{ $goal['formatted_current'] }}</span>
                                        <span class="text-slate-400 text-xs">Alvo: {{ $goal['formatted_target'] }}</span>
                                    </div>

                                    <div class="h-2.5 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ min(100, $goal['progress_percentage']) }}%"></div>
                                    </div>

                                    <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                                        <span>Progresso: <strong class="text-white">{{ $goal['progress_percentage'] }}%</strong></span>
                                        @if($goal['deadline'])
                                            <span>Prazo: {{ $goal['deadline'] }}</span>
                                        @endif
                                    </div>

                                    @if($goal['formatted_monthly_needed'] && !$goal['is_completed'])
                                        <div class="p-2 rounded-lg bg-slate-950/80 border border-slate-800 text-[11px] text-slate-300">
                                            💡 Necessário poupar: <strong class="text-amber-400">{{ $goal['formatted_monthly_needed'] }}/mês</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Contribute Form -->
                            <form method="POST" action="{{ route('savings.contribute', $goal['id']) }}" class="pt-3 border-t border-slate-800 space-y-2">
                                @csrf
                                <div class="flex items-center gap-2">
                                    <input type="text" name="amount" required placeholder="Valor (MZN)" class="flex-1 bg-slate-950 border border-slate-800 rounded-lg p-1.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                                    <select name="account_id" class="bg-slate-950 border border-slate-800 rounded-lg p-1.5 text-[11px] text-slate-300 focus:outline-none">
                                        <option value="">Sem debitar conta</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">Debitar: {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                                        + Poupar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- New Goal Form (4 cols) -->
        <div class="lg:col-span-4">
            <form method="POST" action="{{ route('savings.store') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Nova Meta de Poupança</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nome do Objectivo</label>
                    <input type="text" name="name" required placeholder="Ex: Fundo de Emergência, Casa Própria" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Valor Alvo (MZN)</label>
                    <input type="text" name="target_amount" required placeholder="Ex: 100000" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Data Limite (Prazo Opcional)</label>
                    <input type="date" name="deadline" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Ícone</label>
                    <input type="text" name="icon" value="💰" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Descrição / Finalidade</label>
                    <textarea name="description" rows="2" placeholder="Notas sobre o plano..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                    Criar Meta
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
