<x-layouts.app>
    <x-slot:header>
        Editar Transação
    </x-slot:header>

    <div class="max-w-3xl mx-auto bg-slate-900 rounded-2xl border border-slate-800 p-6 md:p-8">
        <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Transaction Type Tabs -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-2">Tipo de Transação</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-teal-500 has-[:checked]:bg-teal-500/10 has-[:checked]:text-teal-400">
                        <input type="radio" name="type" value="income" class="sr-only" {{ $transaction->type->value === 'income' ? 'checked' : '' }} onchange="toggleTransferFields()">
                        <x-icon name="income" class="w-4 h-4 mr-2" />
                        <span class="text-xs font-bold">Receita</span>
                    </label>

                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 has-[:checked]:text-rose-400">
                        <input type="radio" name="type" value="expense" class="sr-only" {{ $transaction->type->value === 'expense' ? 'checked' : '' }} onchange="toggleTransferFields()">
                        <x-icon name="expense" class="w-4 h-4 mr-2" />
                        <span class="text-xs font-bold">Despesa</span>
                    </label>

                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 has-[:checked]:text-indigo-400">
                        <input type="radio" name="type" value="transfer" class="sr-only" {{ $transaction->type->value === 'transfer' ? 'checked' : '' }} onchange="toggleTransferFields()">
                        <x-icon name="transfer" class="w-4 h-4 mr-2" />
                        <span class="text-xs font-bold">Transferência</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor (MZN)</label>
                    <input type="text" name="amount" required value="{{ number_format($transaction->amount / 100, 2, ',', '.') }}" placeholder="ex: 15.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-base rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data</label>
                    <input type="date" name="transaction_date" required value="{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Descrição</label>
                <input type="text" name="description" required value="{{ $transaction->description }}" placeholder="ex: Salário Mensal, Supermercado" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5" id="sourceAccountLabel">Conta / Origem</label>
                    <select name="account_id" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $transaction->account_id === $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category -->
                <div id="categoryField" class="{{ $transaction->type->value === 'transfer' ? 'hidden' : '' }}">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Categoria</label>
                    <select name="category_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Sem categoria</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $transaction->category_id === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Account (for Transfer) -->
                <div id="transferField" class="{{ $transaction->type->value === 'transfer' ? '' : 'hidden' }}">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Conta Destino</label>
                    <select name="transfer_account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Selecione a conta de destino...</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $transaction->transfer_account_id === $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Notas Adicionais (Opcional)</label>
                <textarea name="notes" rows="3" placeholder="Observações..." class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">{{ $transaction->notes }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20">
                    Atualizar Transação
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleTransferFields() {
            const selectedType = document.querySelector('input[name="type"]:checked').value;
            const categoryField = document.getElementById('categoryField');
            const transferField = document.getElementById('transferField');
            const sourceLabel = document.getElementById('sourceAccountLabel');

            if (selectedType === 'transfer') {
                categoryField.classList.add('hidden');
                transferField.classList.remove('hidden');
                sourceLabel.innerText = 'Conta de Origem';
            } else {
                categoryField.classList.remove('hidden');
                transferField.classList.add('hidden');
                sourceLabel.innerText = 'Conta';
            }
        }
    </script>
</x-layouts.app>
