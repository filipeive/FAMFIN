# FAMFIN — Domain Model

## Entidades de Domínio

```mermaid
classDiagram
    Family "1" --> "*" User
    Family "1" --> "*" Account
    Family "1" --> "*" Category
    Family "1" --> "*" Budget
    Family "1" --> "*" SavingsGoal
    Family "1" --> "*" Debt
    Family "1" --> "*" Asset
    Family "1" --> "*" FinancialRule

    User "1" --> "*" Transaction
    Account "1" --> "*" Transaction
    Category "1" --> "*" Transaction
    Category "1" --> "*" Budget
    Category "0..1" --> "*" Category : parent

    Debt "1" --> "*" DebtPayment
    SavingsGoal "1" --> "*" SavingsContribution

    class Family {
        +id: ulid
        +name: string
        +currency: string
        +timezone: string
    }

    class User {
        +id: ulid
        +family_id: ulid
        +name: string
        +email: string
        +role: enum
    }

    class Account {
        +id: ulid
        +family_id: ulid
        +name: string
        +type: enum
        +balance: integer
        +is_active: boolean
    }

    class Transaction {
        +id: ulid
        +account_id: ulid
        +category_id: ulid
        +user_id: ulid
        +type: enum
        +amount: integer
        +description: string
        +date: date
        +is_recurring: boolean
    }

    class Category {
        +id: ulid
        +family_id: ulid
        +parent_id: ulid?
        +name: string
        +type: enum
        +icon: string
        +color: string
    }

    class Budget {
        +id: ulid
        +family_id: ulid
        +category_id: ulid
        +month: string
        +amount: integer
        +alert_threshold: integer
    }

    class SavingsGoal {
        +id: ulid
        +family_id: ulid
        +name: string
        +target_amount: integer
        +current_amount: integer
        +deadline: date
    }

    class Debt {
        +id: ulid
        +family_id: ulid
        +creditor: string
        +total_amount: integer
        +remaining_amount: integer
        +interest_rate: decimal
        +status: enum
    }

    class Asset {
        +id: ulid
        +family_id: ulid
        +name: string
        +type: enum
        +current_value: integer
        +purchase_value: integer
        +purchase_date: date
    }

    class FinancialRule {
        +id: ulid
        +family_id: ulid
        +type: enum
        +name: string
        +percentage: decimal
        +is_active: boolean
    }
```

---

## Agregados

| Agregado | Root | Entidades Filhas |
|----------|------|-----------------|
| Family | Family | Users |
| Account | Account | Transactions |
| Budget | Budget | — |
| Savings | SavingsGoal | SavingsContribution |
| Debt | Debt | DebtPayment |
| Asset | Asset | — |

---

## Value Objects

| Value Object | Usado em | Descrição |
|-------------|----------|-----------|
| Money | Transaction, Budget, etc. | Valor em centavos |
| DateRange | Budget, Report | Período de datas |
| Percentage | FinancialRule, Debt | Percentual |

---

## Enums

| Enum | Valores |
|------|---------|
| TransactionType | income, expense, transfer |
| AccountType | checking, savings, investment, wallet, credit_card |
| CategoryType | income, expense |
| DebtStatus | active, paid, overdue |
| AssetType | property, vehicle, investment, other |
| UserRole | admin, member, viewer |
| RuleType | tithe, inss, yield, custom |

---

*Última atualização: 2026-09-29*
