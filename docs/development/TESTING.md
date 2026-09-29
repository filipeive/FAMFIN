# FAMFIN — Testing Guide

## Filosofia de Testes

> Toda regra financeira DEVE ter testes automatizados.
> "Funciona na minha tela" NÃO é validação suficiente.

---

## Estrutura

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── TransactionServiceTest.php
│   │   ├── BudgetServiceTest.php
│   │   ├── TaxServiceTest.php
│   │   └── ...
│   ├── Models/
│   │   ├── TransactionTest.php
│   │   └── ...
│   └── Helpers/
│       └── MoneyHelperTest.php
└── Feature/
    ├── TransactionControllerTest.php
    ├── BudgetControllerTest.php
    └── ...
```

---

## O Que Testar

### Obrigatório
- [ ] Cálculo de dízimo
- [ ] Cálculo de INSS
- [ ] Cálculo de rendimento
- [ ] Saldo de conta após transação
- [ ] Saldo de conta após transferência
- [ ] Orçamento consumido
- [ ] Progresso de poupança
- [ ] Amortização de dívida
- [ ] Conversão centavos ↔ reais

### Recomendado
- [ ] CRUD de cada entidade
- [ ] Validação de Form Requests
- [ ] Policies de autorização
- [ ] Tenant isolation (Family scope)

---

## Executar Testes

```bash
# Todos os testes
php artisan test

# Apenas unitários
php artisan test --testsuite=Unit

# Apenas features
php artisan test --testsuite=Feature

# Arquivo específico
php artisan test tests/Unit/Services/TransactionServiceTest.php

# Com coverage
php artisan test --coverage
```

---

## Factories

Usar Model Factories para dados de teste:

```php
Transaction::factory()->income()->create(['amount' => 500000]); // R$ 5.000,00
Transaction::factory()->expense()->create(['amount' => 15000]);  // R$ 150,00
```

---

*Última atualização: 2026-09-29*
