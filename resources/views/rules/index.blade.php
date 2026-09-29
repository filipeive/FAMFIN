@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                ⚙️ Regras Financeiras & Deduções
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Configuração do modelo financeiro (INSS Moçambique, Dízimo e percentuais)</p>
        </div>
    </div>

    <!-- Rules List -->
    <div class="space-y-4">
        @foreach($rules as $rule)
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-white">{{ $rule->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $rule->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400' }}">
                                {{ $rule->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $rule->description }}</p>
                    </div>

                    <!-- Toggle Rule Active Button -->
                    <form method="POST" action="{{ route('rules.toggle', $rule->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded-lg text-xs font-semibold {{ $rule->is_active ? 'bg-slate-800 text-slate-300 hover:bg-slate-700' : 'bg-emerald-500 text-slate-950 hover:bg-emerald-400' }} transition">
                            {{ $rule->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>

                <!-- Update Percentage Form -->
                <form method="POST" action="{{ route('rules.update', $rule->id) }}" class="pt-3 border-t border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center gap-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-2">
                        <label class="font-semibold text-slate-300">Percentagem (%):</label>
                        <input type="number" step="0.01" name="percentage" value="{{ $rule->percentage }}" class="w-24 bg-slate-950 border border-slate-800 rounded-lg p-1.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="apply_{{ $rule->id }}" name="apply_on_income" value="1" {{ $rule->apply_on_income ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-emerald-500 focus:ring-0">
                        <label for="apply_{{ $rule->id }}" class="text-slate-300">Aplicar no cálculo automático de receitas</label>
                    </div>

                    <input type="hidden" name="is_active" value="{{ $rule->is_active ? 1 : 0 }}">

                    <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white transition border border-slate-700 ml-auto">
                        Actualizar Regra
                    </button>
                </form>
            </div>
        @endforeach
    </div>

</div>
@endsection
