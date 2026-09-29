# FAMFIN — Budget Model

## Estrutura do Orçamento

```
Budget (mensal)
├── Categoria: Alimentação → R$ 1.500,00
├── Categoria: Transporte → R$ 800,00
├── Categoria: Lazer → R$ 500,00
├── Categoria: Saúde → R$ 400,00
└── Categoria: Educação → R$ 600,00
```

---

## Regras

1. Orçamento é definido **por categoria, por mês**
2. O consumo é a soma das despesas daquela categoria no mês
3. Alerta em 80% do limite (configurável)
4. Não bloqueia ao atingir 100% (soft limit)
5. Pode ser copiado do mês anterior

---

## Cálculos

```
consumido = SUM(transactions WHERE type='expense' AND category_id=X AND month=Y)
disponível = budget_amount - consumido
percentual = (consumido / budget_amount) × 100
```

---

## Schema

```sql
budgets
├── id
├── family_id
├── category_id
├── month (YYYY-MM)
├── amount (integer, centavos)
├── alert_threshold (integer, percentual, default 80)
├── created_at
└── updated_at
```

---

*Última atualização: 2026-09-29*
