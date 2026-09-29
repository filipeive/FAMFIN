# FAMFIN — Product Specification

## Visão Geral

FAMFIN é um sistema de gestão financeira familiar que permite controlar receitas, despesas, orçamentos, poupanças, dívidas e patrimônio de uma família.

---

## Público-Alvo

- Famílias que desejam organizar suas finanças
- Foco inicial: uma única família (single-tenant)
- Expansão futura: multi-tenant

---

## Funcionalidades Principais

### 1. Dashboard
- Resumo financeiro do mês
- Saldo total das contas
- Receitas vs despesas (gráfico)
- Orçamento consumido (%)
- Alertas (contas a vencer, orçamento estourado)

### 2. Contas (Accounts)
- Cadastro de contas bancárias, carteiras digitais, dinheiro físico
- Saldo atualizado automaticamente por transações
- Transferências entre contas (não é despesa!)

### 3. Transações
- Receitas (salário, rendimentos, extras)
- Despesas (fixas, variáveis, parceladas)
- Categorização obrigatória
- Recorrência (mensal, semanal, etc.)
- Anexos (comprovantes)

### 4. Categorias
- Hierarquia (categoria > subcategoria)
- Categorias padrão pré-configuradas
- Categorias personalizáveis

### 5. Orçamento (Budget)
- Definição mensal por categoria
- Acompanhamento de consumo
- Alertas ao atingir limite

### 6. Poupança (Savings Goals)
- Metas de poupança com prazo
- Contribuições manuais ou automáticas
- Progresso visual

### 7. Dívidas (Debts)
- Registro de dívidas com parcelas
- Amortização antecipada
- Juros e correções
- Status (ativa, quitada, atrasada)

### 8. Patrimônio (Assets)
- Registro de bens (imóveis, veículos, investimentos)
- Valorização/depreciação
- Valor líquido patrimonial

### 9. Regras Financeiras
- **Dízimo**: X% da receita líquida
- **INSS**: Cálculo conforme tabela vigente
- **Rendimento**: Cálculo de CDI/Selic sobre investimentos
- Regras customizáveis

### 10. Relatórios
- Fluxo de caixa mensal
- Despesas por categoria
- Evolução patrimonial
- Comparativo mês a mês

---

## Requisitos Não-Funcionais

- **Segurança**: Dados financeiros sensíveis, HTTPS obrigatório
- **Performance**: Dashboard < 2s
- **Responsividade**: Mobile-first
- **Backup**: Automático diário
- **Offline**: Suporte futuro via NativePHP

---

*Última atualização: 2026-09-29*
