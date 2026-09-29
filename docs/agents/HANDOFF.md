# FAMFIN — Handoff

> **Estado actual do projecto para continuidade de trabalho.**

---

## Estado Actual

**Data**: 2026-09-29
**Fase**: Fase 2 — Interface Web & Controladores de Domínio Concluídos
**Versão**: 0.3.0
**Branch**: main
**Autor**: Filipe dos Santos (Moçambique 🇲🇿)

---

## O que foi feito

- [x] **Setup & Localização**: Laravel 13, Moeda **MZN** (Meticais), Timezone `Africa/Maputo`, Locale `pt_MZ`
- [x] **Domínio Financeiro**: 10 Enums, 12 Models Eloquent, Migrations completas com ULID, Helpers de Dinheiro
- [x] **Domain Services**: `FinancialRuleService`, `BalanceService`, `TransactionService`, `BudgetService`, `SavingsService`, `DebtService`, `ExcelImportService`
- [x] **Importador da Planilha**: 57 meses importados, 968 transacções e 414 orçamentos históricos preservados
- [x] **Controladores Web**:
  - `DashboardController`: Painel com resumo mensal, fluxo de salário (Bruto -> INSS 3% -> Dízimo 10% -> Líquido -> Despesas -> Saldo), Património Líquido, orçamentos e últimas transacções
  - `TransactionController`: Listagem com filtros de busca, paginação, registo e eliminação de receitas/despesas/transferências
  - `BudgetController`: Gestão de tectos por categoria, alertas aos 80% e cópia entre meses
  - `SavingsController`: Metas de poupança, cálculo de necessidade mensal e contribuições
  - `DebtController`: Gestão de dívidas activas, quitação e amortizações
  - `AccountController`: Gestão de contas e recálculo de livro-razão
  - `FinancialRuleController`: Ajuste e activação de percentuais de INSS e Dízimo
- [x] **Frontend Responsivo**: Blade + Tailwind CSS v4 com navegação, cards estatísticos, progress bars, e alertas
- [x] **Testes Automatizados**: **33 testes automatizados** e **118 asserções** (100% aprovados sem falhas)

---

## O que falta fazer

- [ ] Fase 3: Gráficos interactivos de evolução de património e repartição de gastos (Chart.js / ApexCharts)
- [ ] Fase 3: Exportação de relatórios mensais em PDF e CSV
- [ ] Fase 4: Autenticação Multi-Utilizador (Laravel Breeze / Sanctum)
- [ ] Fase 5: Mobile App Offline-First com NativePHP

---

## Decisões Tomadas

| Decisão | Justificativa |
|---------|--------------|
| Laravel 13 + Tailwind CSS v4 | Stack moderna, sem dependências externas no build |
| Moeda MZN (Metical) | Moeda oficial de Moçambique |
| INSS 3% + Dízimo 10% | Fluxo financeiro real mapeado na planilha do Filipe |
| Dinheiro em centavos | Integridade matemática sem perdas decimais |
| Domain Services (DDD) | Controladores magros (thin) e lógica de negócio testável |

---

## Riscos Conhecidos

- Nenhum. Todos os serviços, rotas, telas e testes estão 100% funcionais e validados.

---

## Próximos Passos

1. Adicionar visualizações gráficas de evolução patrimonial e análise de gastos.
2. Adicionar suporte a exportação de relatórios financeiros mensais.

---

*Última actualização: 2026-09-29*
