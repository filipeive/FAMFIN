# FAMFIN — Regras para Agentes AI

> **Leia MASTER_CONTEXT.md primeiro. Depois leia docs/agents/AGENT_GUIDE.md.**

---

## Regra #1: Ordem de Leitura

Antes de qualquer tarefa:

1. `MASTER_CONTEXT.md`
2. `docs/agents/AGENT_GUIDE.md`
3. `docs/product/PRODUCT_SPEC.md`
4. Documentação específica da área da tarefa

---

## Regra #2: Nunca Adivinhar

```
READ → UNDERSTAND → PLAN → IMPLEMENT → TEST → DOCUMENT → HANDOFF
```

Nunca: `GUESS → CODE`

---

## Regra #3: Domínio Financeiro

- Lógica financeira vive em `app/Services/`
- Nunca em Controllers ou Views
- Dinheiro em centavos (integer)
- Verificar `docs/finance/FINANCIAL_RULES.md` antes de alterar cálculos

---

## Regra #4: Testes

- Toda alteração financeira exige testes
- "Funciona na minha tela" não é validação

---

## Regra #5: Git

- Nunca force push
- Nunca apagar branches
- Nunca resetar trabalho de outro agente
- Sempre `git status` e `git diff` antes de commit

---

## Regra #6: Output

Ao concluir, informar:

```
## Implemented
## Files Changed
## Tests
## Documentation Updated
## Known Issues
## Next Step
```

---

## Documentação Completa

Consultar: `docs/agents/AGENT_GUIDE.md`
