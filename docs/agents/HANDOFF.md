# FAMFIN — Handoff

> **Estado atual do projeto para continuidade de trabalho.**

---

## Estado Atual

**Data**: 2026-10-22  
**Fase**: Fase 6 Concluída (Mobile UI/UX Refinement, Grid Views, Deduções Automáticas de Dízimo/INSS, Ícone Nativo & APK Android)  
**Versão**: 1.0.0  
**Branch**: main  
**Testes**: 58 testes / 291 asserções (100% de aprovação)  
**Produção**: `http://146.235.224.99/famfin/` (Oracle Cloud)  
**APK Android**: `FAMFIN-v1.0.apk` (4.3 MB)

---

## O que foi feito

- [x] **Arquitetura Mobile-First em Grelha (Grid Views)** inspirada no padrão do `proj_edificar`:
  - **Transações (`transactions/index.blade.php`)**: Comutador Alpine.js reativo entre **Grelha** e **Lista**, com padrão automático em **Grelha** em ecrãs mobile (`< 768px`). Cada transação é um cartão touch com ícone temático, badges de tipo/deduções, tipografia destacada em moeda e botões de ação rápida. Seleção em massa com sincronização entre grelha e lista.
  - **Dashboard (`dashboard.blade.php`)**: Barra de atalhos rápidos touch em 4 colunas no mobile (Receita, Despesa, Transferir, Poupar), cartões de métricas KPI em grelha 2x2 no mobile (4 colunas no desktop) e transações recentes renderizadas como cartões mobile com zero overflow horizontal.
  - **Categorias (`categories/index.blade.php`)**: Listagem convertida em grelha de cartões responsivos (`grid-cols-1 sm:grid-cols-2`) com gestão de subcategorias e ações imediatas.
  - **Orçamentos (`budgets/index.blade.php`)**: Grelha de cartões com barras de progresso, percentagens de consumo e alertas visuais por categoria.
  - **Poupanças & Metas (`savings/index.blade.php`)**: Cartões de objetivos em grelha responsiva com histórico recente de aportes e botão direto de contribuição.
  - **Dívidas (`debts/index.blade.php`)**: Cartões de amortização em grelha com barra de liquidação, cálculo de parcelas e amortização via modal.
- [x] **Dedução Automática de Dízimo e INSS**:
  - `TransactionService::createIncomeWithDeductions()` executa criação atómica de receita e despesas vinculadas (Dízimo 10% e INSS 3%).
  - Identificação automática por palavras-chave ("salário", "life child").
  - Confirmação interativa com SweetAlert2 em MZN em tempo real.
- [x] **Segurança e Autenticação Nativa**:
  - Proteção de todas as rotas financeiras via middleware `auth` com redirecionamento de convidados para `/login`.
  - Tratamento do botão físico "Voltar" (Hardware Back Button) no Android via `@capacitor/app`.
- [x] **Ícone Nativo e Identidade Visual**:
  - Geração completa de ícones mipmap adaptativos (fundo `#020617` + squircle esmeralda/ouro) para Android em todas as resoluções.
  - Compilação e publicação do pacote APK `FAMFIN-v1.0.apk`.

---

## Decisões Tomadas

| Decisão | Justificativa |
|---------|--------------|
| **Vista Grelha Prioritária no Mobile** | Elimina scrolls horizontais de tabelas em smartphones e oferece experiência nativa de app bancária/fintech |
| **Alpine.js Grid/List Switcher** | Permite ao utilizador alternar instantaneamente entre grelha de cartões e tabela desktop com memória local |
| **KPIs em 2 Colunas no Mobile** | Proporciona visualização compacta e equilibrada de Património, Receitas, Despesas e Saldo sem forçar scroll longo |
| **Barra de Ações Rápidas no Dashboard** | Facilita registo com um único toque das operações mais frequentes no telemóvel |

---

## Suíte de Testes

- 58 testes executados com `php artisan test`
- 291 asserções validadas com sucesso (100% de aprovação)
