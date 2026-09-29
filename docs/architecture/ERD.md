# FAMFIN — Entity Relationship Diagram

## ERD

```mermaid
erDiagram
    families ||--o{ users : "has"
    families ||--o{ accounts : "has"
    families ||--o{ categories : "has"
    families ||--o{ budgets : "has"
    families ||--o{ savings_goals : "has"
    families ||--o{ debts : "has"
    families ||--o{ assets : "has"
    families ||--o{ financial_rules : "has"

    users ||--o{ transactions : "creates"
    accounts ||--o{ transactions : "has"
    categories ||--o{ transactions : "classifies"
    categories ||--o{ budgets : "budgeted by"
    categories ||--o{ categories : "parent of"

    debts ||--o{ debt_payments : "has"
    savings_goals ||--o{ savings_contributions : "has"

    families {
        ulid id PK
        string name
        string currency
        string timezone
        timestamp created_at
        timestamp updated_at
    }

    users {
        ulid id PK
        ulid family_id FK
        string name
        string email
        string password
        enum role
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    accounts {
        ulid id PK
        ulid family_id FK
        string name
        enum type
        integer balance
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    transactions {
        ulid id PK
        ulid account_id FK
        ulid category_id FK
        ulid user_id FK
        ulid transfer_account_id FK "nullable"
        enum type
        integer amount
        string description
        date transaction_date
        boolean is_recurring
        string recurrence_pattern "nullable"
        timestamp created_at
        timestamp updated_at
    }

    categories {
        ulid id PK
        ulid family_id FK
        ulid parent_id FK "nullable"
        string name
        enum type
        string icon "nullable"
        string color "nullable"
        integer sort_order
        timestamp created_at
        timestamp updated_at
    }

    budgets {
        ulid id PK
        ulid family_id FK
        ulid category_id FK
        string month
        integer amount
        integer alert_threshold
        timestamp created_at
        timestamp updated_at
    }

    savings_goals {
        ulid id PK
        ulid family_id FK
        string name
        integer target_amount
        integer current_amount
        date deadline "nullable"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    savings_contributions {
        ulid id PK
        ulid savings_goal_id FK
        integer amount
        date contribution_date
        string note "nullable"
        timestamp created_at
        timestamp updated_at
    }

    debts {
        ulid id PK
        ulid family_id FK
        string creditor
        integer total_amount
        integer remaining_amount
        decimal interest_rate
        enum interest_type
        integer installments_total
        integer installments_paid
        date start_date
        date due_date "nullable"
        enum status
        timestamp created_at
        timestamp updated_at
    }

    debt_payments {
        ulid id PK
        ulid debt_id FK
        integer amount
        integer principal
        integer interest
        date payment_date
        integer installment_number
        timestamp created_at
        timestamp updated_at
    }

    assets {
        ulid id PK
        ulid family_id FK
        string name
        enum type
        integer current_value
        integer purchase_value
        date purchase_date
        string description "nullable"
        timestamp created_at
        timestamp updated_at
    }

    financial_rules {
        ulid id PK
        ulid family_id FK
        enum type
        string name
        decimal percentage
        boolean is_active
        json config "nullable"
        timestamp created_at
        timestamp updated_at
    }
```

---

## Notas

- Todos os IDs usam ULID (ordenável por tempo)
- Todos os valores monetários em centavos (integer)
- `transfer_account_id` em transactions: apenas para tipo `transfer`
- `parent_id` em categories: permite hierarquia de categorias
- `config` em financial_rules: JSON para parâmetros específicos do tipo de regra

---

*Última atualização: 2026-09-29*
