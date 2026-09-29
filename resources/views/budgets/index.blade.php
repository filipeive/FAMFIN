@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Controls -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                🎯 Orçamentos Mensais
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Planeamento e controlo de tectos de gastos por categoria</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Month Switcher -->
            <form method="GET" action="{{ route('budgets.index') }}" class="flex items-center gap-1.5 bg-slate-900 p-1 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 pl-2">📅 Mês:</span>
                <select name="month" onchange="this.form.submit()" class="bg-slate-950 text-white text-xs font-semibold rounded-lg px-2.5 py-1 border border-slate-700 focus:outline-none">
                    @foreach($availableMonths as $m)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Summary Overview Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Total Orçado ({{ $month }})</span>
            <p class="text-2xl font-black text-white mt-1">{{ $overview['formatted_total_budgeted'] }}</p>
        </div>
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Total Consumido</span>
            <p class="text-2xl font-black text-rose-400 mt-1">{{ $overview['formatted_total_spent'] }}</p>
            <span class="text-[10px] text-slate-400">{{ $overview['overall_percentage'] }}% do tecto</span>
        </div>
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold">Disponível Restante</span>
            <p class="text-2xl font-black {{ $overview['total_remaining'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                {{ $overview['formatted_total_remaining'] }}
            </p>
        </div>
    </div>

    <!-- Main Content: Budget Allocation List + New Budget Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Budget List (8 cols) -->
        <div class="lg:col-span-8 bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
            <h3 class="text-base font-bold text-white">Categorias Orçadas em {{ $month }}</h3>

            @if(empty($overview['items']))
                <div class="text-center py-12 text-slate-400 text-xs">
                    Nenhum orçamento configurado para {{ $month }}. Defina ao lado ou copie de um mês anterior.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($overview['items'] as $item)
                        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $item['category_icon'] ?? '🏷️' }}</span>
                                    <span class="font-bold text-white text-sm">{{ $item['category_name'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-white">{{ $item['formatted_spent'] }}</span>
                                    <span class="text-slate-400">/ {{ $item['formatted_budget'] }}</span>
                                    <span class="font-semibold text-xs ml-2 {{ $item['status'] === 'exceeded' ? 'text-rose-400' : ($item['status'] === 'warning' ? 'text-amber-400' : 'text-emerald-400') }}">
                                        {{ $item['percentage_used'] }}%
                                    </span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="h-2.5 w-full bg-slate-900 rounded-full overflow-hidden border border-slate-800">
                                @php
                                    $width = min(100, $item['percentage_used']);
                                    $barColor = $item['status'] === 'exceeded' ? 'bg-rose-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500');
                                @endphp
                                <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $width }}%"></div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                                <span>Restante: <strong class="{{ $item['remaining_amount'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $item['formatted_remaining'] }}</strong></span>
                                <span>Alerta aos {{ $item['alert_threshold'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Forms Column (4 cols) -->
        <div class="lg:col-span-4 space-y-5">
            <!-- Add / Update Budget -->
            <form method="POST" action="{{ route('budgets.store') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Definir Orçamento</h3>

                <input type="hidden" name="month" value="{{ $month }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Categoria de Despesa</label>
                    <select name="category_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Tecto Mensal (MZN)</label>
                    <input type="text" name="amount" required placeholder="Ex: 5000" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Alerta em (%)</label>
                    <input type="number" name="alert_threshold" value="80" min="1" max="100" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                    Guardar Orçamento
                </button>
            </form>

            <!-- Copy Budgets from Another Month -->
            <form method="POST" action="{{ route('budgets.copy') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Copiar Orçamentos</h3>
                <p class="text-[11px] text-slate-400">Replicar todo o planeamento de um mês anterior para {{ $month }}:</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Mês de Origem</label>
                    <select name="from_month" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:border-emerald-500 focus:outline-none">
                        @foreach($availableMonths as $m)
                            @if($m !== $month)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="to_month" value="{{ $month }}">

                <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white transition border border-slate-700">
                    Copiar para {{ $month }}
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
