# FAMFIN — Agent Guide

> **Guia obrigatório para qualquer agente AI que trabalhe neste projeto.**

---

## Antes de fazer qualquer coisa

Leia nesta ordem:

1. `MASTER_CONTEXT.md`
2. `docs/agents/AGENT_GUIDE.md` (este ficheiro)
3. `docs/product/PRODUCT_SPEC.md`
4. Documentação específica da área da tarefa

Depois:

```bash
git status
git branch --show-current
git log --oneline -10
```

---

## 1. NÃO ASSUMA

Não assuma que:

- O código está correto
- A documentação está atualizada
- A planilha foi modelada corretamente
- Uma categoria é uma entidade
- Uma transferência é uma despesa
- Poupança é despesa
- Uma regra financeira é uma transação
- Uma solução existente deve ser mantida

**Verifique.**

---

## 2. ANTES DE CODIFICAR

Responder internamente:

- What is the business problem?
- What domain entity is involved?
- What existing code handles this?
- What documentation defines it?
- What can this change break?
- What tests are required?

Para mudanças relevantes, criar um plano antes da implementação.

---

## 3. REGRAS FINANCEIRAS

Nunca alterar sem verificar os serviços de domínio e os testes existentes:

- Cálculo de rendimento
- INSS
- Dízimo
- Orçamento
- Saldo
- Poupança
- Dívida
- Patrimônio

**Não duplicar cálculo em Controllers ou frontend.**

---

## 4. DOCUMENTAÇÃO

| Quando | Ação |
|--------|------|
| Mudança arquitetural | Atualizar docs relevantes |
| Decisão importante | Criar/atualizar ADR em `docs/architecture/ADR/` |
| Mudança que outro agente precisará | Atualizar `docs/agents/HANDOFF.md` |

---

## 5. TESTES

- Toda alteração financeira deve incluir ou atualizar testes
- Nunca considerar "funciona na minha tela" como validação suficiente
- Testes unitários em `tests/Unit/`
- Testes de integração em `tests/Feature/`

---

## 6. MOBILE

Antes de criar lógica mobile:

1. Ler `docs/mobile/MOBILE_ARCHITECTURE.md`
2. Ler `docs/mobile/OFFLINE_FIRST.md`
3. Ler `docs/mobile/SYNC_ARCHITECTURE.md`

**Não duplicar Financial Domain para NativePHP.**

---

## 7. GIT

Nunca:

- Resetar trabalho de outro agente sem autorização
- Apagar branches
- Fazer force push
- Sobrescrever mudanças desconhecidas
- Remover documentação sem compreender sua finalidade

Antes de commit:

```bash
git status
git diff
```

---

## 8. DEPENDÊNCIAS

Antes de instalar uma biblioteca:

1. Verificar se Laravel/NativePHP já resolve
2. Verificar dependências existentes
3. Avaliar manutenção e segurança
4. Justificar a instalação

Não instalar bibliotecas apenas para pequenas conveniências.

---

## 9. OUTPUT DE CADA TAREFA

Ao terminar uma tarefa, informar:

```
## Implemented
## Files Changed
## Tests
## Documentation Updated
## Known Issues
## Next Step
```

---

## 10. HANDOFF

Se outro agente continuar o trabalho, atualizar `docs/agents/HANDOFF.md` com o estado real do projeto.

---

## PRINCÍPIO

```
READ → UNDERSTAND → PLAN → IMPLEMENT → TEST → DOCUMENT → HANDOFF
```

Nunca:

```
GUESS → CODE
```
