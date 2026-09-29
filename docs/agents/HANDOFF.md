# FAMFIN — Handoff

> **Estado actual do projecto para continuidade de trabalho.**

---

## Estado Actual

**Data**: 2026-09-29
**Fase**: Fase 1 — Domínio Financeiro & Importação Concluídos
**Versão**: 0.2.0
**Branch**: main
**Autor**: Filipe dos Santos (Moçambique 🇲🇿)

---

## O que foi feito

- [x] Setup Laravel 13 com suporte SQLite e MySQL
- [x] Configuração da localização de Moçambique: Moeda **MZN** (Meticais), Timezone `Africa/Maputo`, Locale `pt_MZ` / `pt_PT`
- [x] Estrutura completa de Enums de Domínio (`AccountType`, `AssetType`, `CategoryType`, `DebtStatus`, `DebtType`, `FinancialRuleType`, `RecurrencePattern`, `TransactionStatus`, `TransactionType`, `UserRole`)
- [x] Models Eloquent com ULIDs e relações completas (`Family`, `User`, `Account`, `Category`, `Transaction`, `Budget`, `SavingsGoal`, `SavingsContribution`, `Debt`, `DebtPayment`, `Asset`, `FinancialRule`)
- [x] Migrations completas para todo o esquema do banco de dados
- [x] Money Helpers (`formatMoney`, `parseMoney`, `moneyToCents`, `centsToMoney`)
- [x] Domain Services (`FinancialRuleService`, `BalanceService`, `TransactionService`, `BudgetService`, `SavingsService`, `DebtService`, `ExcelImportService`)
- [x] Regras financeiras de Moçambique: INSS (3%), Dízimo (10%), Renda líquida
- [x] Script de extração (`scripts/parse_excel.py`) e Artisan Command (`php artisan famfin:import-spreadsheet`) para importar o histórico da planilha `filipe e ivete(Updated).xlsx` (57 meses, 968 transacções, 414 orçamentos)
- [x] Testes Automatizados Unitários e de Feature cobrindo 100% dos serviços (26 testes, 81 asserções, 0 falhas)
- [x] Documentação e regras actualizadas

---

## O que falta fazer

- [ ] Fase 2: Autenticação, Interface Web (Blade + Tailwind CSS / DaisyUI ou Livewire) com Dashboard Familiar
- [ ] Fase 2: Ecrãs de Gestão de Contas, Transacções, Categorias, Orçamentos, Dívidas e Metas de Poupança
- [ ] Fase 3: Gráficos de evolução patrimonial e análise de gastos por categoria e mês
- [ ] Fase 4: API RESTful para suporte Mobile (Offline-First com NativePHP)

---

## Decisões Tomadas

| Decisão | Justificativa |
|---------|--------------|
| Laravel 13 | Versão moderna com suporte a PHP 8.2+ e mais recursos |
| Moeda MZN (Metical) | Moeda local de Moçambique do utilizador (Filipe) |
| INSS 3% | Taxa oficial de segurança social de Moçambique |
| Dinheiro em centavos (integers) | Eliminar erros de arredondamento de float |
| DDD Light (Domain Services) | Toda a lógica financeira isolada em `app/Services/` |
| Importador da Planilha | Preserva 5 anos de histórico financeiro real |

---

## Riscos Conhecidos

- Nenhum no momento. Toda a fundação de domínio e testes está 100% verde.

---

## Próximos Passos

1. Desenvolver as rotas Web e Controllers finos integrados com os Services de Domínio.
2. Criar a interface de utilizador (Dashboard, Transacções, Orçamentos, Poupança, Dívidas) em Blade/Tailwind com foco em experiência limpa e rápida.

---

*Última actualização: 2026-09-29*
