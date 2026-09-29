# FAMFIN — Architecture

## Visão Geral

```
┌─────────────────────────────────────────┐
│               Frontend                   │
│         Blade + Vite + Alpine.js         │
├─────────────────────────────────────────┤
│              Controllers                 │
│          (thin, delegation only)         │
├─────────────────────────────────────────┤
│            Form Requests                 │
│          (input validation)              │
├─────────────────────────────────────────┤
│           Domain Services                │
│        (business logic lives here)       │
├─────────────────────────────────────────┤
│          Eloquent Models                 │
│      (data access + relationships)       │
├─────────────────────────────────────────┤
│              MySQL                       │
│         (persistent storage)             │
└─────────────────────────────────────────┘
```

---

## Princípios Arquiteturais

### 1. Controllers são finos
```php
// ✅ CORRETO
public function store(StoreTransactionRequest $request)
{
    $transaction = $this->transactionService->create($request->validated());
    return redirect()->route('transactions.show', $transaction);
}

// ❌ ERRADO
public function store(Request $request)
{
    // 50 linhas de lógica de negócio aqui...
}
```

### 2. Lógica de negócio nos Services
```
app/Services/
├── TransactionService.php
├── BudgetService.php
├── SavingsService.php
├── DebtService.php
├── TaxService.php
├── InvestmentService.php
└── BalanceService.php
```

### 3. Validação nos Form Requests
```
app/Http/Requests/
├── StoreTransactionRequest.php
├── UpdateTransactionRequest.php
├── StoreBudgetRequest.php
└── ...
```

### 4. Enums para constantes de domínio
```
app/Enums/
├── TransactionType.php
├── AccountType.php
├── CategoryType.php
├── DebtStatus.php
├── AssetType.php
├── UserRole.php
└── RuleType.php
```

---

## Camadas

| Camada | Responsabilidade | Diretório |
|--------|-----------------|-----------|
| Presentation | Views, Components | `resources/views/` |
| HTTP | Routes, Controllers, Middleware | `routes/`, `app/Http/` |
| Validation | Form Requests | `app/Http/Requests/` |
| Business | Services, Events | `app/Services/`, `app/Events/` |
| Domain | Models, Enums, Policies | `app/Models/`, `app/Enums/` |
| Data | Migrations, Seeders | `database/` |

---

## Comunicação entre Camadas

```
Request → Route → Middleware → Controller → FormRequest → Service → Model → Database
                                                              ↓
                                                           Event → Listener
```

---

*Última atualização: 2026-09-29*
