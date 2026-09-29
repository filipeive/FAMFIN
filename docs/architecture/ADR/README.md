# FAMFIN — Architecture Decision Records

## ADR Index

| # | Título | Status | Data |
|---|--------|--------|------|
| 001 | Usar centavos para valores monetários | Aceito | 2026-09-29 |
| 002 | Laravel 13 como framework | Aceito | 2026-09-29 |
| 003 | MySQL como banco de dados | Aceito | 2026-09-29 |
| 004 | Domain Services para lógica de negócio | Aceito | 2026-09-29 |

---

## ADR-001: Usar centavos para valores monetários

**Status**: Aceito
**Data**: 2026-09-29

### Contexto
Valores monetários precisam ser precisos. Ponto flutuante causa erros de arredondamento.

### Decisão
Todos os valores monetários são armazenados como inteiros representando centavos.

### Consequências
- ✅ Precisão garantida
- ✅ Sem problemas de float
- ⚠️ Necessário converter para exibição
- ⚠️ Necessário converter de input

---

## ADR-002: Laravel 13

**Status**: Aceito
**Data**: 2026-09-29

### Contexto
Necessidade de um framework PHP robusto com ecossistema completo.

### Decisão
Usar Laravel 13 (versão mais recente) para aproveitar as últimas features.

### Consequências
- ✅ Ecossistema completo (auth, queue, cache, etc.)
- ✅ NativePHP para mobile futuro
- ✅ Comunidade ativa

---

## ADR-003: MySQL

**Status**: Aceito
**Data**: 2026-09-29

### Contexto
Necessidade de banco relacional confiável para dados financeiros.

### Decisão
MySQL como banco de dados principal.

### Consequências
- ✅ Confiável para dados financeiros
- ✅ Suporte a transações ACID
- ✅ Amplamente suportado

---

## ADR-004: Domain Services

**Status**: Aceito
**Data**: 2026-09-29

### Contexto
Lógica financeira complexa não deve ficar em Controllers.

### Decisão
Usar Services de domínio para toda lógica de negócio.

### Consequências
- ✅ Testabilidade
- ✅ Reutilização (web + API + mobile)
- ✅ Separação de responsabilidades
- ⚠️ Mais ficheiros no projeto

---

*Última atualização: 2026-09-29*
