<x-layouts.app>
    <x-slot:header>
        Editar Dívida / Crédito
    </x-slot:header>

    <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-white">Editar Dados da Dívida</h3>
            <p class="text-xs text-slate-400">Atualize os valores, taxas e estado de amortização.</p>
        </div>

        <form method="POST" action="{{ route('debts.update', $debt) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Credor / Instituição</label>
                    <input type="text" name="creditor" value="{{ old('creditor', $debt->creditor) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo</label>
                    <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($debtTypes as $type)
                            <option value="{{ $type->value }}" {{ $debt->type->value === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Descrição</label>
                <input type="text" name="description" value="{{ old('description', $debt->description) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Inicial Contratado (MZN)</label>
                    <input type="text" name="original_amount" value="{{ number_format($debt->original_amount / 100, 2, ',', '.') }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Saldo Devedor Restante (MZN)</label>
                    <input type="text" name="remaining_amount" value="{{ number_format($debt->remaining_amount / 100, 2, ',', '.') }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Parcela Mensal (MZN)</label>
                    <input type="text" name="monthly_payment" value="{{ $debt->monthly_payment ? number_format($debt->monthly_payment / 100, 2, ',', '.') : '' }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Taxa de Juro (%)</label>
                    <input type="number" step="0.01" name="interest_rate" value="{{ $debt->interest_rate }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Estado</label>
                    <select name="status" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($debtStatuses as $st)
                            <option value="{{ $st->value }}" {{ $debt->status->value === $st->value ? 'selected' : '' }}>{{ ucfirst($st->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data de Vencimento Final</label>
                <input type="date" name="due_date" value="{{ $debt->due_date ? \Carbon\Carbon::parse($debt->due_date)->format('Y-m-d') : '' }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Observações</label>
                <textarea name="notes" rows="3" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">{{ $debt->notes }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-slate-800">
                <form method="POST" action="{{ route('debts.destroy', $debt) }}" onsubmit="return confirm('Deseja realmente eliminar esta dívida?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-rose-500/20 text-rose-400 hover:bg-rose-500/10 text-xs font-semibold transition">
                        Eliminar Dívida
                    </button>
                </form>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('debts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20">
                        Guardar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
