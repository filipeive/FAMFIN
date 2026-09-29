@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white">Nova Transacção</h1>
            <p class="text-xs text-slate-400 mt-0.5">Registar receita, despesa ou transferência</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Voltar</a>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-6 space-y-5 shadow-xl">
        @csrf

        <!-- Type Selector -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipo de Movimento</label>
            <div class="grid grid-cols-3 gap-3">
                <label class="cursor-pointer border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center transition hover:bg-slate-800 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-950/30">
                    <input type="radio" name="type" value="expense" class="sr-only" checked onchange="handleTypeChange('expense')">
                    <span class="text-lg mb-1">💸</span>
                    <span class="text-xs font-bold text-slate-200">Despesa</span>
                </label>
                <label class="cursor-pointer border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center transition hover:bg-slate-800 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/30">
                    <input type="radio" name="type" value="income" class="sr-only" onchange="handleTypeChange('income')">
                    <span class="text-lg mb-1">💵</span>
                    <span class="text-xs font-bold text-slate-200">Receita</span>
                </label>
                <label class="cursor-pointer border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center transition hover:bg-slate-800 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-950/30">
                    <input type="radio" name="type" value="transfer" class="sr-only" onchange="handleTypeChange('transfer')">
                    <span class="text-lg mb-1">⇄</span>
                    <span class="text-xs font-bold text-slate-200">Transferência</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Valor (MZN)</label>
                <input type="text" name="amount" required placeholder="Ex: 5000 ou 5.000,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white font-mono focus:border-emerald-500 focus:outline-none">
            </div>

            <!-- Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Data</label>
                <input type="date" name="transaction_date" required value="{{ now()->toDateString() }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none">
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Descrição</label>
            <input type="text" name="description" required placeholder="Ex: Rancho do mês, Salário, Electricidade" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Source Account -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1" id="account_label">Conta</label>
                <select name="account_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none">
                    @foreach($family->accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->icon }} {{ $acc->name }} (Saldo: {{ $acc->formatted_balance }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Transfer Target Account (Hidden by default) -->
            <div id="transfer_account_wrapper" class="hidden">
                <label class="block text-xs font-semibold text-slate-300 mb-1">Conta de Destino</label>
                <select name="transfer_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none">
                    <option value="">Seleccione a conta destino</option>
                    @foreach($family->accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->icon }} {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category (Shown for Income & Expense) -->
            <div id="category_wrapper">
                <label class="block text-xs font-semibold text-slate-300 mb-1">Categoria</label>
                <select name="category_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none">
                    <option value="">Sem categoria</option>
                    @foreach($family->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Notas adicionais (opcional)</label>
            <textarea name="notes" rows="2" placeholder="Observações..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-800 flex justify-end gap-3">
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-800 transition">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition shadow-lg shadow-emerald-950">
                Guardar Transacção
            </button>
        </div>
    </form>

</div>

<script>
    function handleTypeChange(type) {
        const catWrapper = document.getElementById('category_wrapper');
        const transferWrapper = document.getElementById('transfer_account_wrapper');
        const accLabel = document.getElementById('account_label');

        if (type === 'transfer') {
            catWrapper.classList.add('hidden');
            transferWrapper.classList.remove('hidden');
            accLabel.textContent = 'Conta de Origem';
        } else {
            catWrapper.classList.remove('hidden');
            transferWrapper.classList.add('hidden');
            accLabel.textContent = 'Conta';
        }
    }
</script>
@endsection
