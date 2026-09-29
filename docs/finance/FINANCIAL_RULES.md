# FAMFIN — Financial Rules

## Princípio Fundamental

> Todo valor monetário é armazenado em **centavos** (integer).
> `100,50 MT` / `100.50 MZN` → `10050` no banco de dados.

---

## Regras de Negócio

### 1. Dízimo

```
dízimo = receita_líquida × (percentual / 100)
```

- Percentual configurável (padrão: 10%)
- Aplicado sobre receita (bruta ou líquida após INSS, configurável)
- Calculado automaticamente ao registar receita
- Pode ser desativado por família

### 2. INSS (Moçambique)

```
inss = salario_bruto × 3%
```

- Taxa fixa de 3% para o trabalhador (conforme legislação de segurança social de Moçambique)
- Configurável nas regras da família
- Calculado automaticamente ao registar receita do tipo salário

### 3. Rendimento / Poupança / Investimento

```
rendimento_diário = saldo × (taxa_anual / 365)
```

- Taxa de juro anual ou taxa fixa configurável
- Calculado via scheduled job
- Aplicado em contas de investimento ou poupança

### 4. Orçamento

```
consumido = SUM(despesas_categoria_no_mês)
disponível = orçamento_mensal - consumido
percentual = (consumido / orçamento_mensal) × 100
```

- Alerta em 80% (configurável)
- Bloqueio soft em 100% (aviso, não impede)

### 5. Saldo de Conta

```
saldo = saldo_inicial + SUM(receitas) - SUM(despesas)
```

- Saldo recalculado a cada transação
- Transferência: débito na origem, crédito no destino (saldo total inalterado)
- **Transferência NÃO é despesa**

### 6. Poupança

```
progresso = (total_contribuído / meta) × 100
contribuição_mensal_necessária = (meta - total_contribuído) / meses_restantes
```

- Meta com valor alvo e prazo
- Contribuições manuais ou automáticas
- **Poupança NÃO é despesa** (é reserva)

### 7. Dívida

```
total_pago = SUM(parcelas_pagas)
saldo_devedor = valor_total + juros_acumulados - total_pago
```

- Parcelas com vencimento
- Juros compostos ou simples (configurável)
- Amortização antecipada recalcula juros

---

## Regras de Validação

| Regra | Validação |
|-------|-----------|
| Valor mínimo de transação | > 0 centavos |
| Conta com saldo negativo | Aviso (não bloqueia) |
| Despesa sem categoria | Rejeitada |
| Transferência para mesma conta | Rejeitada |
| Data futura em despesa | Marcada como "agendada" |
| Dízimo sem receita | Não aplicável |

---

## Arredondamento

- Sempre arredondar para **baixo** (floor) quando houver fração de centavo
- Diferença absorvida em "ajuste de arredondamento"
- Nunca usar `float` para dinheiro

---

## Serviços de Domínio

Toda lógica financeira deve estar em `app/Services/`:

| Serviço | Responsabilidade |
|---------|-----------------|
| `TransactionService` | Criar/atualizar transações, recalcular saldos |
| `BudgetService` | Verificar orçamento, gerar alertas |
| `SavingsService` | Contribuições, progresso, projeções |
| `DebtService` | Parcelas, amortização, juros |
| `TaxService` | INSS, dízimo, regras fiscais |
| `InvestmentService` | Rendimentos, CDI/Selic |
| `BalanceService` | Recálculo de saldos de conta |

---

*Última atualização: 2026-09-29*
