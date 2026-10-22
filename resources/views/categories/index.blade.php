<x-layouts.app>
    <x-slot:header>
        Categorias Financeiras
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Categories List (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Expense Categories -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                            <x-icon name="expense" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Categorias de Despesa</h3>
                            <p class="text-xs text-slate-400">{{ $expenseCategories->count() }} categorias principais configuradas</p>
                        </div>
                    </div>
                </div>

                <!-- Grid de Cartões de Despesas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @forelse ($expenseCategories as $cat)
                        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/60 hover:border-slate-600 transition flex flex-col justify-between shadow-sm">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center space-x-2.5 min-w-0">
                                        <span class="w-3.5 h-3.5 rounded-full ring-2 ring-slate-800 flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-sm text-slate-100 truncate">{{ $cat->name }}</h4>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $cat->transactions_count }} transações</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1 flex-shrink-0">
                                        <a href="{{ route('categories.edit', $cat) }}" title="Editar" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        </a>

                                        <form method="POST" action="{{ route('categories.destroy', $cat) }}" id="delete-cat-{{ $cat->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction('delete-cat-{{ $cat->id }}', 'Eliminar Categoria?', 'Deseja realmente eliminar \'{{ $cat->name }}\'? As transações serão mantidas.', 'Sim, Eliminar')"
                                                    title="Eliminar" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if ($cat->children && $cat->children->count() > 0)
                                    <div class="mt-3 pt-2.5 border-t border-slate-700/50 space-y-1.5 bg-slate-950/40 p-2.5 rounded-lg">
                                        <div class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-400 mb-1">
                                            <span>Subcategorias ({{ $cat->children->count() }})</span>
                                            <form method="POST" action="{{ route('categories.destroy_children', $cat) }}" id="clear-children-{{ $cat->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        onclick="confirmAction('clear-children-{{ $cat->id }}', 'Limpar Subcategorias?', 'Deseja desvincular todas as subcategorias de {{ $cat->name }}?', 'Sim, Limpar')"
                                                        title="Limpar subcategorias" 
                                                        class="text-[9px] text-amber-400 hover:text-amber-300">
                                                    Desvincular todas
                                                </button>
                                            </form>
                                        </div>
                                        <div class="space-y-1">
                                            @foreach ($cat->children as $child)
                                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-slate-800/40 hover:bg-slate-800 transition">
                                                    <span class="text-slate-300 font-medium truncate max-w-[120px]">{{ $child->name }}</span>
                                                    <div class="flex items-center space-x-1">
                                                        <span class="text-[10px] text-slate-500 mr-1">{{ $child->transactions_count }} tr.</span>
                                                        <form method="POST" action="{{ route('categories.unlink', $child) }}" id="unlink-subcat-{{ $child->id }}">
                                                            @csrf
                                                            <button type="button" 
                                                                    onclick="confirmAction('unlink-subcat-{{ $child->id }}', 'Tirar da lista?', 'Deseja desvincular \'{{ $child->name }}\' desta categoria superior?', 'Sim, Tirar da lista')"
                                                                    title="Tirar da lista (Desvincular)" 
                                                                    class="p-1 text-slate-400 hover:text-amber-400 rounded">
                                                                <x-icon name="close" class="w-3 h-3" />
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('categories.edit', $child) }}" title="Editar" class="p-1 text-slate-500 hover:text-emerald-400 rounded">
                                                            <x-icon name="pencil" class="w-3 h-3" />
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-slate-500 text-xs">
                            Nenhuma categoria de despesa cadastrada.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Income Categories -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                            <x-icon name="income" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Categorias de Receita</h3>
                            <p class="text-xs text-slate-400">{{ $incomeCategories->count() }} categorias principais configuradas</p>
                        </div>
                    </div>
                </div>

                <!-- Grid de Cartões de Receitas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @forelse ($incomeCategories as $cat)
                        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/60 hover:border-slate-600 transition flex flex-col justify-between shadow-sm">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center space-x-2.5 min-w-0">
                                        <span class="w-3.5 h-3.5 rounded-full ring-2 ring-slate-800 flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-sm text-slate-100 truncate">{{ $cat->name }}</h4>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $cat->transactions_count }} transações</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1 flex-shrink-0">
                                        <a href="{{ route('categories.edit', $cat) }}" title="Editar" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        </a>

                                        <form method="POST" action="{{ route('categories.destroy', $cat) }}" id="delete-inc-{{ $cat->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction('delete-inc-{{ $cat->id }}', 'Eliminar Categoria?', 'Deseja realmente eliminar \'{{ $cat->name }}\'?', 'Sim, Eliminar')"
                                                    title="Eliminar" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if ($cat->children && $cat->children->count() > 0)
                                    <div class="mt-3 pt-2.5 border-t border-slate-700/50 space-y-1.5 bg-slate-950/40 p-2.5 rounded-lg">
                                        <div class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-400 mb-1">
                                            <span>Subcategorias ({{ $cat->children->count() }})</span>
                                            <form method="POST" action="{{ route('categories.destroy_children', $cat) }}" id="clear-children-inc-{{ $cat->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        onclick="confirmAction('clear-children-inc-{{ $cat->id }}', 'Limpar Subcategorias?', 'Deseja desvincular todas as subcategorias de {{ $cat->name }}?', 'Sim, Limpar')"
                                                        title="Limpar subcategorias" 
                                                        class="text-[9px] text-amber-400 hover:text-amber-300">
                                                    Desvincular todas
                                                </button>
                                            </form>
                                        </div>
                                        <div class="space-y-1">
                                            @foreach ($cat->children as $child)
                                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-slate-800/40 hover:bg-slate-800 transition">
                                                    <span class="text-slate-300 font-medium truncate max-w-[120px]">{{ $child->name }}</span>
                                                    <div class="flex items-center space-x-1">
                                                        <span class="text-[10px] text-slate-500 mr-1">{{ $child->transactions_count }} tr.</span>
                                                        <form method="POST" action="{{ route('categories.unlink', $child) }}" id="unlink-subcat-inc-{{ $child->id }}">
                                                            @csrf
                                                            <button type="button" 
                                                                    onclick="confirmAction('unlink-subcat-inc-{{ $child->id }}', 'Tirar da lista?', 'Deseja desvincular \'{{ $child->name }}\' desta categoria?', 'Sim, Tirar da lista')"
                                                                    title="Tirar da lista (Desvincular)" 
                                                                    class="p-1 text-slate-400 hover:text-amber-400 rounded">
                                                                <x-icon name="close" class="w-3 h-3" />
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('categories.edit', $child) }}" title="Editar" class="p-1 text-slate-500 hover:text-emerald-400 rounded">
                                                            <x-icon name="pencil" class="w-3 h-3" />
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-slate-500 text-xs">
                            Nenhuma categoria de receita cadastrada.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Create Category Form (1 col) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 h-fit shadow-sm">
            <h3 class="text-base font-bold text-white mb-4">Nova Categoria</h3>

            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Categoria</label>
                    <input type="text" name="name" required placeholder="ex: Saúde & Farmácia, Lazer" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo</label>
                    <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="expense">Despesa</option>
                        <option value="income">Receita</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Categoria Superior (Pai) - Opcional</label>
                    <select name="parent_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Nenhuma (Categoria Principal)</option>
                        @foreach ($parentCategories as $pCat)
                            <option value="{{ $pCat->id }}">{{ $pCat->name }} ({{ ucfirst($pCat->type->value) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Cor de Destaque</label>
                    <div class="flex items-center space-x-2">
                        <input type="color" name="color" value="#10B981" class="w-10 h-10 rounded-lg bg-transparent border-0 cursor-pointer">
                        <span class="text-xs text-slate-400">Escolha uma cor para os gráficos</span>
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs py-3 px-4 rounded-xl transition shadow-lg shadow-emerald-500/20 active:scale-95">
                    Cadastrar Categoria
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
