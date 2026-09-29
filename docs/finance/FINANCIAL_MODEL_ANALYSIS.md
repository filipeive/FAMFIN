# FAMFIN — Financial Model Analysis

## Modelo Financeiro

O FAMFIN opera com um modelo financeiro centrado na **família** como unidade de gestão.

---

## Fluxo de Dinheiro

```
Receita (salário, rendimentos, extras)
    │
    ├── Descontos obrigatórios (INSS)
    ├── Regras financeiras (dízimo)
    │
    └── Receita Líquida
         │
         ├── Despesas fixas
         ├── Despesas variáveis
         ├── Contribuição para poupança
         ├── Pagamento de dívidas
         │
         └── Saldo disponível
```

---

## Entidades Financeiras

### Contas (Accounts)
- Representam onde o dinheiro está
- Tipos: corrente, poupança, investimento, carteira, cartão
- Cada conta tem um saldo calculado

### Transações (Transactions)
- Representam movimentos de dinheiro
- Tipos: receita (income), despesa (expense), transferência (transfer)
- Sempre vinculadas a uma conta e uma categoria

### Categorias (Categories)
- Classificam transações
- Hierárquicas (pai/filho)
- Base para orçamento

### Orçamento (Budget)
- Limite mensal por categoria
- Monitoramento de consumo

---

## Distinções Importantes

| Conceito | É | Não é |
|----------|---|-------|
| Transferência | Movimento entre contas | Despesa |
| Poupança | Reserva/meta | Despesa |
| Dízimo | Regra financeira | Transação automática |
| INSS | Desconto obrigatório | Despesa da família |
| Rendimento | Receita de investimento | Saldo estático |

---

*Última atualização: 2026-09-29*
