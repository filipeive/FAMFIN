# FAMFIN — API Documentation

## Convenções

- **Base URL**: `/api/v1`
- **Formato**: JSON
- **Autenticação**: Sanctum (Bearer token)
- **Valores monetários**: Sempre em centavos (integer)
- **Datas**: ISO 8601 (`YYYY-MM-DD`)
- **IDs**: ULID

---

## Endpoints (Planejados)

### Accounts
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/accounts` | Listar contas |
| POST | `/api/v1/accounts` | Criar conta |
| GET | `/api/v1/accounts/{id}` | Ver conta |
| PUT | `/api/v1/accounts/{id}` | Atualizar conta |
| DELETE | `/api/v1/accounts/{id}` | Desativar conta |

### Transactions
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/transactions` | Listar transações |
| POST | `/api/v1/transactions` | Criar transação |
| GET | `/api/v1/transactions/{id}` | Ver transação |
| PUT | `/api/v1/transactions/{id}` | Atualizar transação |
| DELETE | `/api/v1/transactions/{id}` | Remover transação |

### Categories
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/categories` | Listar categorias |
| POST | `/api/v1/categories` | Criar categoria |
| PUT | `/api/v1/categories/{id}` | Atualizar categoria |
| DELETE | `/api/v1/categories/{id}` | Remover categoria |

### Budgets
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/budgets` | Listar orçamentos |
| POST | `/api/v1/budgets` | Criar orçamento |
| PUT | `/api/v1/budgets/{id}` | Atualizar orçamento |

### Dashboard
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/dashboard/summary` | Resumo financeiro |
| GET | `/api/v1/dashboard/cashflow` | Fluxo de caixa |

---

## Formato de Resposta

```json
{
    "data": { ... },
    "meta": {
        "current_page": 1,
        "total": 50
    }
}
```

## Formato de Erro

```json
{
    "message": "Validation failed",
    "errors": {
        "amount": ["O valor é obrigatório."]
    }
}
```

---

*Última atualização: 2026-09-29*
