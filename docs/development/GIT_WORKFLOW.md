# FAMFIN — Git Workflow

## Branches

| Branch | Uso |
|--------|-----|
| `main` | Produção estável |
| `develop` | Desenvolvimento ativo |
| `feature/<name>` | Nova funcionalidade |
| `fix/<name>` | Correção de bug |
| `docs/<name>` | Documentação |

---

## Fluxo

```
main ← develop ← feature/xxx
```

1. Criar branch a partir de `develop`
2. Implementar
3. Testar
4. Merge para `develop`
5. Quando estável, merge `develop` → `main`

---

## Commits

Formato: `type(scope): description`

```
feat(transactions): add recurring transactions
fix(budget): correct percentage calculation
docs(finance): update financial rules
refactor(services): extract balance calculation
test(tax): add INSS calculation tests
chore(deps): update laravel to 13.x
```

---

## Regras

- Nunca force push em `main` ou `develop`
- Nunca resetar trabalho de outro agente
- Nunca apagar branches sem confirmar
- Sempre `git status` + `git diff` antes de commit
- Mensagens de commit em inglês

---

*Última atualização: 2026-09-29*
