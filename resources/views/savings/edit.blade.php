<x-layouts.app>
    <x-slot:header>
        Editar Meta de Poupança
    </x-slot:header>

    <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-white">Editar Dados da Meta</h3>
            <p class="text-xs text-slate-400">Atualize o montante pretendido, o prazo de conclusão e o estado da meta.</p>
        </div>

        <form method="POST" action="{{ route('savings.update', $goal) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome do Objetivo</label>
                <input type="text" name="name" value="{{ old('name', $goal->name) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Alvo (MZN)</label>
                <input type="text" name="target_amount" value="{{ number_format($goal->target_amount / 100, 2, ',', '.') }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data Limite</label>
                <input type="date" name="deadline" value="{{ $goal->deadline ? \Carbon\Carbon::parse($goal->deadline)->format('Y-m-d') : '' }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Observações</label>
                <textarea name="notes" rows="3" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">{{ $goal->notes }}</textarea>
            </div>

            <div class="flex items-center pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ $goal->is_active ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                <label for="is_active" class="ml-2 text-xs text-slate-300">Meta ativa</label>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-slate-800">
                <form method="POST" action="{{ route('savings.destroy', $goal) }}" onsubmit="return confirm('Deseja realmente eliminar esta meta e todas as suas contribuições?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-rose-500/20 text-rose-400 hover:bg-rose-500/10 text-xs font-semibold transition">
                        Eliminar Meta
                    </button>
                </form>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('savings.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
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
