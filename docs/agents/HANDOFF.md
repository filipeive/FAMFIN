# FAMFIN — Handoff

> **Estado atual do projeto para continuidade de trabalho.**

---

## Estado Atual

**Data**: 2026-10-04
**Fase**: Fase 5 Concluída (Saúde Financeira, Notificações, CRUDs Completos, Layout Full-Width, SweetAlert2)
**Versão**: 0.5.0
**Branch**: main
**Testes**: 54 testes / 264 asserções (100% de aprovação)

---

## O que foi feito

- [x] Setup Laravel 13 e migrações de dados SQLite / MySQL
- [x] Modelos de domínio completos (Family, User, Account, Transaction, Category, Budget, SavingsGoal, SavingsContribution, Debt, DebtPayment, Asset, FinancialRule)
- [x] Serviços financeiros com cálculo em centavos (BalanceService, TransactionService, BudgetService, SavingsService, DebtService, FinancialRuleService, FinancialHealthService, NotificationService, ReportService, ExcelImportService)
- [x] Dashboard dinâmico integrado com seleção de período histórico e cartões de património, receitas, despesas e saldo operacional
- [x] Transações: CRUD completo, paginação, filtros por tipo/conta/categoria/período/busca textual e eliminação em massa (Bulk Delete) com recálculo atómico de saldos
- [x] Contas bancárias, Bens & Imóveis (Património), Dívidas e Metas de Poupança com formulários modais e edição/remoção
- [x] Layout escuro (slate-950/slate-900) 100% full-width sem limitação de largura no painel direito
- [x] Navbar espaçada com navegação clara, atalho de perfil e botão de terminar sessão com confirmação interativa
- [x] Integração SweetAlert2 com tema escuro (toasts para feedback de sessão e caixas de diálogo modais para confirmações de remoção e logout)
- [x] Autenticação com sessão persistente e suporte a navegação livre em modo desenvolvimento
- [x] Importação histórica de 57 meses de dados a partir de planilha Excel

---

## Decisões Tomadas

| Decisão | Justificativa |
|---------|--------------|
| Layout Full-Width | Removido `max-w-7xl` do `<main>` para permitir que o conteúdo ocupe toda a área visível do ecrã |
| SweetAlert2 Escuro | Alertas e toasts estilizados em dark mode (`#0f172a`/`#1e293b`), substituindo diálogos nativos do browser |
| Dinheiro em centavos | Valores monetários inteiros garantem precisão matemática em todas as agregações financeiras |
| SoftDeletes em Transações | Permite recuperação e histórico sem perda acidental de dados |
| Ambientes Python Isolados | `.venv` com `openpyxl` local no projeto para execução de parsers de folha de cálculo |

---

## Suíte de Testes

- 54 testes executados com `php artisan test`
- 264 asserções validadas com sucesso
- Cobertura de transações, orçamentos, saúde financeira, dívidas, poupanças, relatórios, importação e eliminação em massa
