<x-layouts.app>
    <x-slot:header>
        Categorias Financeiras
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Categories List (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Expense Categories -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                            <x-icon name="expense" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Categorias de Despesa</h3>
                            <p class="text-xs text-slate-400">{{ $expenseCategories->count() }} categorias principais configuradas</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-800/80">
                    @forelse ($expenseCategories as $cat)
                        <div class="py-3.5 flex flex-col space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3.5 h-3.5 rounded-full ring-2 ring-slate-800 flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                                    <div>
                                        <span class="font-bold text-sm text-slate-100">{{ $cat->name }}</span>
                                        <span class="text-xs text-slate-500 font-medium ml-1.5">({{ $cat->transactions_count }} transações)</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    @if ($cat->children && $cat->children->count() > 0)
                                        <form method="POST" action="{{ route('categories.destroy_children', $cat) }}" id="clear-children-{{ $cat->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction('clear-children-{{ $cat->id }}', 'Limpar Subcategorias?', 'Deseja desvincular todas as {{ $cat->children->count() }} subcategorias de {{ $cat->name }}?', 'Sim, Limpar')"
                                                    title="Limpar todas as subcategorias" 
                                                    class="px-2 py-1 text-[11px] font-semibold rounded-lg text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 transition">
                                                Limpar Subcategorias
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('categories.edit', $cat) }}" title="Editar" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>

                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" id="delete-cat-{{ $cat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmAction('delete-cat-{{ $cat->id }}', 'Eliminar Categoria?', 'Deseja realmente eliminar \'{{ $cat->name }}\'? As transações serão mantidas.', 'Sim, Eliminar')"
                                                title="Eliminar" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if ($cat->children && $cat->children->count() > 0)
                                <div class="pl-4 py-1.5 space-y-1.5 border-l-2 border-slate-800 ml-1.5 bg-slate-950/40 rounded-r-xl pr-2">
                                    @foreach ($cat->children as $child)
                                        <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg hover:bg-slate-800/50 transition group">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-slate-500 group-hover:text-slate-400">&bull;</span>
                                                <span class="text-slate-300 font-medium group-hover:text-white">{{ $child->name }}</span>
                                                <span class="text-[10px] text-slate-500">({{ $child->transactions_count }} tr.)</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <!-- Tirar da Lista (Unlink) -->
                                                <form method="POST" action="{{ route('categories.unlink', $child) }}" id="unlink-subcat-{{ $child->id }}">
                                                    @csrf
                                                    <button type="button" 
                                                            onclick="confirmAction('unlink-subcat-{{ $child->id }}', 'Tirar da lista?', 'Deseja desvincular \'{{ $child->name }}\' desta categoria superior?', 'Sim, Tirar da lista')"
                                                            title="Tirar da lista (Desvincular)" 
                                                            class="px-2 py-0.5 text-[10px] font-medium rounded text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition">
                                                        Tirar da lista
                                                    </button>
                                                </form>

                                                <a href="{{ route('categories.edit', $child) }}" title="Editar" class="p-1 text-slate-500 hover:text-emerald-400 rounded">
                                                    <x-icon name="pencil" class="w-3.5 h-3.5" />
                                                </a>

                                                <form method="POST" action="{{ route('categories.destroy', $child) }}" id="delete-subcat-{{ $child->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            onclick="confirmAction('delete-subcat-{{ $child->id }}', 'Eliminar Subcategoria?', 'Deseja eliminar definitivamente \'{{ $child->name }}\'?', 'Sim, Eliminar')"
                                                            title="Eliminar" 
                                                            class="p-1 text-slate-500 hover:text-rose-400 rounded">
                                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-500 text-xs">Nenhuma categoria de despesa cadastrada.</p>
                    @endforelse
                </div>
            </div>

            <!-- Income Categories -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                            <x-icon name="income" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Categorias de Receita</h3>
                            <p class="text-xs text-slate-400">{{ $incomeCategories->count() }} categorias principais configuradas</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-800/80">
                    @forelse ($incomeCategories as $cat)
                        <div class="py-3.5 flex flex-col space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3.5 h-3.5 rounded-full ring-2 ring-slate-800 flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                                    <div>
                                        <span class="font-bold text-sm text-slate-100">{{ $cat->name }}</span>
                                        <span class="text-xs text-slate-500 font-medium ml-1.5">({{ $cat->transactions_count }} transações)</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    @if ($cat->children && $cat->children->count() > 0)
                                        <form method="POST" action="{{ route('categories.destroy_children', $cat) }}" id="clear-children-inc-{{ $cat->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction('clear-children-inc-{{ $cat->id }}', 'Limpar Subcategorias?', 'Deseja desvincular todas as subcategorias de {{ $cat->name }}?', 'Sim, Limpar')"
                                                    title="Limpar subcategorias" 
                                                    class="px-2 py-1 text-[11px] font-semibold rounded-lg text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 transition">
                                                Limpar Subcategorias
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('categories.edit', $cat) }}" title="Editar" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>

                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" id="delete-inc-{{ $cat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmAction('delete-inc-{{ $cat->id }}', 'Eliminar Categoria?', 'Deseja realmente eliminar \'{{ $cat->name }}\'?', 'Sim, Eliminar')"
                                                title="Eliminar" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if ($cat->children && $cat->children->count() > 0)
                                <div class="pl-4 py-1.5 space-y-1.5 border-l-2 border-slate-800 ml-1.5 bg-slate-950/40 rounded-r-xl pr-2">
                                    @foreach ($cat->children as $child)
                                        <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg hover:bg-slate-800/50 transition group">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-slate-500 group-hover:text-slate-400">&bull;</span>
                                                <span class="text-slate-300 font-medium group-hover:text-white">{{ $child->name }}</span>
                                                <span class="text-[10px] text-slate-500">({{ $child->transactions_count }} tr.)</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <form method="POST" action="{{ route('categories.unlink', $child) }}" id="unlink-subcat-inc-{{ $child->id }}">
                                                    @csrf
                                                    <button type="button" 
                                                            onclick="confirmAction('unlink-subcat-inc-{{ $child->id }}', 'Tirar da lista?', 'Deseja desvincular \'{{ $child->name }}\' desta categoria?', 'Sim, Tirar da lista')"
                                                            title="Tirar da lista (Desvincular)" 
                                                            class="px-2 py-0.5 text-[10px] font-medium rounded text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition">
                                                        Tirar da lista
                                                    </button>
                                                </form>

                                                <a href="{{ route('categories.edit', $child) }}" title="Editar" class="p-1 text-slate-500 hover:text-emerald-400 rounded">
                                                    <x-icon name="pencil" class="w-3.5 h-3.5" />
                                                </a>

                                                <form method="POST" action="{{ route('categories.destroy', $child) }}" id="delete-subcat-inc-{{ $child->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            onclick="confirmAction('delete-subcat-inc-{{ $child->id }}', 'Eliminar Subcategoria?', 'Deseja eliminar \'{{ $child->name }}\'?', 'Sim, Eliminar')"
                                                            title="Eliminar" 
                                                            class="p-1 text-slate-500 hover:text-rose-400 rounded">
                                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-500 text-xs">Nenhuma categoria de receita cadastrada.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Create Category Form (1 col) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit shadow-sm">
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

                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs py-3 px-4 rounded-xl transition shadow-lg shadow-emerald-500/20">
                    Cadastrar Categoria
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
