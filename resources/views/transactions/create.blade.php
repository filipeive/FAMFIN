<x-layouts.app>
    <x-slot:header>
        Registar Nova Transação
    </x-slot:header>

    <div class="max-w-3xl mx-auto bg-slate-900 rounded-2xl border border-slate-800 p-5 sm:p-6 md:p-8 shadow-xl">
        <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm" class="space-y-6">
            @csrf

            <!-- Transaction Type Tabs -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-2">Tipo de Transação</label>
                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-teal-500 has-[:checked]:bg-teal-500/10 has-[:checked]:text-teal-400">
                        <input type="radio" name="type" value="income" class="sr-only" {{ request('type', 'income') === 'income' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <x-icon name="income" class="w-4 h-4 mr-1.5 sm:mr-2" />
                        <span class="text-xs font-bold">Receita</span>
                    </label>

                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 has-[:checked]:text-rose-400">
                        <input type="radio" name="type" value="expense" class="sr-only" {{ request('type') === 'expense' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <x-icon name="expense" class="w-4 h-4 mr-1.5 sm:mr-2" />
                        <span class="text-xs font-bold">Despesa</span>
                    </label>

                    <label class="flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 has-[:checked]:text-indigo-400">
                        <input type="radio" name="type" value="transfer" class="sr-only" {{ request('type') === 'transfer' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <x-icon name="transfer" class="w-4 h-4 mr-1.5 sm:mr-2" />
                        <span class="text-xs font-bold">Transferência</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor (MZN)</label>
                    <input type="text" name="amount" id="amountInput" required placeholder="ex: 80.000,00 ou 80000" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-base rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Description -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-400">Descrição</label>
                    <span id="detectedBadge" class="hidden text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        💼 Life Child Detetada
                    </span>
                </div>
                <input type="text" name="description" id="descriptionInput" required placeholder="ex: Salário Life Child, Consultoria, Supermercado" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <!-- Account -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5" id="sourceAccountLabel">Conta / Origem</label>
                    <select name="account_id" id="accountSelect" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Selecione a conta...</option>
                        <optgroup label="🏦 Contas Principais">
                            @foreach ($accounts->where('is_allowance', false) as $acc)
                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})
                                </option>
                            @endforeach
                        </optgroup>
                        @if ($accounts->where('is_allowance', true)->count() > 0)
                            <optgroup label="👧 Subcontas / Mesadas das Filhas (Ayume & Eunice)">
                                @foreach ($accounts->where('is_allowance', true) as $acc)
                                    <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} &bull; Saldo: {{ formatMoney($acc->balance, 'MZN') }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Category -->
                <div id="categoryField">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Categoria</label>
                    <select name="category_id" id="categorySelect" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Sem categoria (auto-detetar)</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type->value ?? $cat->type }}" data-name="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Account (for Transfer) -->
                <div id="transferField" class="hidden">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Conta Destino</label>
                    <select name="transfer_account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Selecione a conta de destino...</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Deductions Panel (Income Only) -->
            <div id="deductionsPanel" class="p-4 rounded-xl bg-slate-800/70 border border-slate-700/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-200 flex items-center gap-1.5">
                            <span class="text-base">⚡</span> Deduções Automáticas Recomendadas
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5" id="deductionsHint">
                            Regras financeiras para dízimo e obrigações sociais.
                        </p>
                    </div>
                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-300">
                        Automático
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <!-- Dízimo Toggle -->
                    <label class="flex items-start p-3 rounded-xl border border-slate-700 bg-slate-900/60 cursor-pointer hover:border-purple-500/60 transition group">
                        <input type="checkbox" name="deduct_tithe" value="1" id="titheCheckbox" class="mt-0.5 rounded bg-slate-800 border-slate-600 text-purple-600 focus:ring-purple-500 focus:ring-offset-slate-900" onchange="updateDeductionsPreview()">
                        <div class="ml-2.5 flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200 group-hover:text-purple-300">Dízimo (10%)</span>
                                <span class="text-xs font-extrabold text-purple-400 font-mono" id="previewTithe">0,00 MZN</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Contribuição espiritual familiar</p>
                        </div>
                    </label>

                    <!-- INSS Toggle (Highlighted for Life Child) -->
                    <label id="inssWrapper" class="flex items-start p-3 rounded-xl border border-slate-700 bg-slate-900/60 cursor-pointer hover:border-blue-500/60 transition group">
                        <input type="checkbox" name="deduct_inss" value="1" id="inssCheckbox" class="mt-0.5 rounded bg-slate-800 border-slate-600 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900" onchange="updateDeductionsPreview()">
                        <div class="ml-2.5 flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200 group-hover:text-blue-300">INSS (3%)</span>
                                <span class="text-xs font-extrabold text-blue-400 font-mono" id="previewInss">0,00 MZN</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5" id="inssDescriptionText">Dedução legal de segurança social</p>
                        </div>
                    </label>
                </div>

                <!-- Net Amount Preview Banner -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-xs">
                    <span class="text-slate-400 font-medium">Saldo Líquido Estimado na Conta:</span>
                    <span class="font-extrabold text-emerald-400 text-sm font-mono" id="previewNet">0,00 MZN</span>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Notas Adicionais (Opcional)</label>
                <textarea name="notes" rows="2" placeholder="Observações..." class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20 active:scale-95">
                    Registar Transação
                </button>
            </div>
        </form>
    </div>

    <script>
        let isConfirmedSubmission = false;

        function parseAmount(val) {
            if (!val) return 0;
            let clean = val.toString().replace(/\s/g, '').replace('MZN', '').trim();
            // Handle European/PT format: 1.000,50 -> 1000.50
            if (clean.includes(',') && clean.includes('.')) {
                clean = clean.replace(/\./g, '').replace(',', '.');
            } else if (clean.includes(',')) {
                clean = clean.replace(',', '.');
            }
            const num = parseFloat(clean);
            return isNaN(num) ? 0 : num;
        }

        function formatMzn(num) {
            return new Intl.NumberFormat('pt-MZ', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num) + ' MZN';
        }

        function handleTypeChange() {
            const selectedType = document.querySelector('input[name="type"]:checked').value;
            const categoryField = document.getElementById('categoryField');
            const transferField = document.getElementById('transferField');
            const sourceLabel = document.getElementById('sourceAccountLabel');
            const deductionsPanel = document.getElementById('deductionsPanel');

            if (selectedType === 'transfer') {
                categoryField.classList.add('hidden');
                transferField.classList.remove('hidden');
                sourceLabel.innerText = 'Conta de Origem';
                deductionsPanel.classList.add('hidden');
            } else if (selectedType === 'expense') {
                categoryField.classList.remove('hidden');
                transferField.classList.add('hidden');
                sourceLabel.innerText = 'Conta';
                deductionsPanel.classList.add('hidden');
            } else {
                categoryField.classList.remove('hidden');
                transferField.classList.add('hidden');
                sourceLabel.innerText = 'Conta de Depósito';
                deductionsPanel.classList.remove('hidden');
                detectIncomeContext();
            }
        }

        function detectIncomeContext() {
            const desc = (document.getElementById('descriptionInput').value || '').toLowerCase();
            const categorySelect = document.getElementById('categorySelect');
            const titheCheckbox = document.getElementById('titheCheckbox');
            const inssCheckbox = document.getElementById('inssCheckbox');
            const detectedBadge = document.getElementById('detectedBadge');
            const inssWrapper = document.getElementById('inssWrapper');
            const deductionsHint = document.getElementById('deductionsHint');

            const isLifeChild = desc.includes('life child') || desc.includes('lifechild') || desc.includes('life');
            const isSalary = desc.includes('salario') || desc.includes('salário') || isLifeChild;

            // Auto-select Salary category if empty and salary is typed
            if (isSalary && !categorySelect.value) {
                for (let i = 0; i < categorySelect.options.length; i++) {
                    const opt = categorySelect.options[i];
                    const name = (opt.getAttribute('data-name') || '').toLowerCase();
                    if (name.includes('salário') || name.includes('salario')) {
                        categorySelect.selectedIndex = i;
                        break;
                    }
                }
            }

            if (isLifeChild) {
                detectedBadge.classList.remove('hidden');
                inssWrapper.classList.remove('opacity-50', 'pointer-events-none');
                deductionsHint.innerText = 'Salário Life Child: Dedução de Dízimo (10%) e INSS (3%) recomendada.';
                // Pre-check if not manually toggled off
                if (!titheCheckbox.dataset.userChanged) titheCheckbox.checked = true;
                if (!inssCheckbox.dataset.userChanged) inssCheckbox.checked = true;
            } else {
                detectedBadge.classList.add('hidden');
                if (isSalary) {
                    deductionsHint.innerText = 'Salário: Dedução de Dízimo (10%) e INSS (3%) disponíveis.';
                    inssWrapper.classList.remove('opacity-50', 'pointer-events-none');
                    if (!titheCheckbox.dataset.userChanged) titheCheckbox.checked = true;
                } else {
                    deductionsHint.innerText = 'Receita geral: Dedução de Dízimo (10%) recomendada.';
                    inssWrapper.classList.add('opacity-50');
                    if (!inssCheckbox.dataset.userChanged) inssCheckbox.checked = false;
                    if (!titheCheckbox.dataset.userChanged) titheCheckbox.checked = true;
                }
            }

            updateDeductionsPreview();
        }

        function updateDeductionsPreview() {
            const amount = parseAmount(document.getElementById('amountInput').value);
            const titheCheckbox = document.getElementById('titheCheckbox');
            const inssCheckbox = document.getElementById('inssCheckbox');

            const titheVal = titheCheckbox.checked ? (amount * 0.10) : 0;
            const inssVal = inssCheckbox.checked ? (amount * 0.03) : 0;
            const netVal = Math.max(0, amount - titheVal - inssVal);

            document.getElementById('previewTithe').innerText = formatMzn(amount * 0.10);
            document.getElementById('previewInss').innerText = formatMzn(amount * 0.03);
            document.getElementById('previewNet').innerText = formatMzn(netVal > 0 ? netVal : amount);
        }

        document.getElementById('titheCheckbox').addEventListener('change', function() {
            this.dataset.userChanged = 'true';
            updateDeductionsPreview();
        });

        document.getElementById('inssCheckbox').addEventListener('change', function() {
            this.dataset.userChanged = 'true';
            updateDeductionsPreview();
        });

        document.getElementById('descriptionInput').addEventListener('input', detectIncomeContext);
        document.getElementById('amountInput').addEventListener('input', updateDeductionsPreview);

        // Smart Confirmation on Submit
        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            if (isConfirmedSubmission) return;

            const selectedType = document.querySelector('input[name="type"]:checked').value;
            if (selectedType !== 'income') return;

            const desc = (document.getElementById('descriptionInput').value || '').toLowerCase();
            const titheChecked = document.getElementById('titheCheckbox').checked;
            const inssChecked = document.getElementById('inssCheckbox').checked;
            const amount = parseAmount(document.getElementById('amountInput').value);

            const isLifeChild = desc.includes('life child') || desc.includes('lifechild') || desc.includes('life');

            // If it is Life Child and deductions are not checked, prompt nicely
            if (isLifeChild && (!titheChecked || !inssChecked)) {
                e.preventDefault();
                const titheAmt = formatMzn(amount * 0.10);
                const inssAmt = formatMzn(amount * 0.03);
                const netAmt = formatMzn(amount * 0.87);

                Swal.fire({
                    title: 'Salário Life Child',
                    html: `
                        <div class="text-left text-xs space-y-3">
                            <p class="text-slate-300">Detectámos que esta receita é um <strong>Salário da Life Child</strong>. Deseja aplicar as deduções recomendadas?</p>
                            <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/80 space-y-1.5 font-mono">
                                <div class="flex justify-between text-purple-400"><span>• Dízimo (10%):</span><span>${titheAmt}</span></div>
                                <div class="flex justify-between text-blue-400"><span>• INSS (3%):</span><span>${inssAmt}</span></div>
                                <div class="flex justify-between text-emerald-400 font-bold border-t border-slate-700 pt-1"><span>• Líquido:</span><span>${netAmt}</span></div>
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Deduzir Ambos (13%)',
                    denyButtonText: 'Apenas Dízimo (10%)',
                    cancelButtonText: 'Sem Deduções',
                    confirmButtonColor: '#10b981',
                    denyButtonColor: '#8b5cf6',
                    cancelButtonColor: '#475569',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('titheCheckbox').checked = true;
                        document.getElementById('inssCheckbox').checked = true;
                    } else if (result.isDenied) {
                        document.getElementById('titheCheckbox').checked = true;
                        document.getElementById('inssCheckbox').checked = false;
                    } else {
                        document.getElementById('titheCheckbox').checked = false;
                        document.getElementById('inssCheckbox').checked = false;
                    }
                    isConfirmedSubmission = true;
                    document.getElementById('transactionForm').submit();
                });
                return;
            }

            // If other income and tithe is not checked, ask if they want to tithe
            if (!isLifeChild && !titheChecked && amount > 0) {
                e.preventDefault();
                const titheAmt = formatMzn(amount * 0.10);

                Swal.fire({
                    title: 'Deduzir Dízimo (10%)?',
                    html: `
                        <div class="text-left text-xs space-y-2">
                            <p class="text-slate-300">Deseja gerar a dedução automática de <strong>10% (${titheAmt})</strong> para Dízimos & Contribuições nesta receita?</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, Deduzir Dízimo',
                    cancelButtonText: 'Não, Registar Bruto',
                    confirmButtonColor: '#8b5cf6',
                    cancelButtonColor: '#475569',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('titheCheckbox').checked = true;
                    }
                    isConfirmedSubmission = true;
                    document.getElementById('transactionForm').submit();
                });
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            handleTypeChange();
        });
    </script>
</x-layouts.app>
