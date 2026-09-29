# FAMFIN — Master Context

> **Leia isto PRIMEIRO. Este é o documento raiz do projeto.**

---

## O que é o FAMFIN

FAMFIN (Family Finance) é um sistema de gestão financeira familiar construído em **Laravel 13** com plano futuro de aplicação mobile via **NativePHP**.

O sistema permite que uma família gerencie:

- **Receitas** (salários, rendimentos, extras)
- **Despesas** (fixas, variáveis, parceladas)
- **Orçamentos** (por categoria, por mês)
- **Poupança** (metas de poupança com acompanhamento)
- **Dívidas** (controle de dívidas, amortizações)
- **Patrimônio** (bens, investimentos)
- **Regras financeiras** (dízimo, INSS, rendimento)

---

## Stack Tecnológica

| Componente     | Tecnologia         |
|----------------|-------------------|
| Backend        | Laravel 13 (PHP)  |
| Database       | MySQL             |
| Frontend       | Blade + Vite      |
| Mobile (futuro)| NativePHP         |
| Cache/Queue    | Database (MySQL)  |
| Auth           | Laravel Breeze/Sanctum |

---

## Princípios Fundamentais

1. **Domain-Driven Design** — Lógica financeira vive nos Services de domínio, nunca em Controllers ou Views.
2. **Dinheiro como centavos** — Todo valor monetário é armazenado em centavos (integer) para evitar problemas de ponto flutuante.
3. **Offline-First (mobile)** — A arquitetura mobile deve funcionar sem internet e sincronizar depois.
4. **Testes obrigatórios** — Toda regra financeira deve ter testes automatizados.
5. **Documentação viva** — A documentação é parte do código, não um artefato separado.

---

## Estrutura do Projeto

```
FAMFIN/
├── MASTER_CONTEXT.md          ← Você está aqui
├── AGENTS.md                  ← Regras para agentes AI
├── README.md                  ← Guia de início rápido
├── CHANGELOG.md               ← Registro de mudanças
│
├── app/
│   ├── Models/                ← Eloquent Models
│   ├── Services/              ← Domain Services (lógica de negócio)
│   ├── Http/Controllers/      ← Controllers (thin)
│   ├── Http/Requests/         ← Form Requests (validação)
│   ├── Enums/                 ← Enums de domínio
│   ├── Events/                ← Domain Events
│   ├── Listeners/             ← Event Listeners
│   ├── Policies/              ← Authorization Policies
│   └── Exceptions/            ← Custom Exceptions
│
├── database/
│   ├── migrations/            ← Schema migrations
│   ├── seeders/               ← Seeders
│   └── factories/             ← Model Factories
│
├── resources/
│   ├── views/                 ← Blade templates
│   ├── css/                   ← Stylesheets
│   └── js/                    ← JavaScript
│
├── routes/
│   ├── web.php                ← Web routes
│   └── api.php                ← API routes
│
├── tests/
│   ├── Unit/                  ← Unit tests
│   └── Feature/               ← Feature/integration tests
│
└── docs/                      ← Documentação completa
    ├── product/               ← Especificação do produto
    ├── finance/               ← Regras financeiras
    ├── architecture/          ← Arquitetura técnica
    ├── mobile/                ← Arquitetura mobile
    ├── development/           ← Guias de desenvolvimento
    └── agents/                ← Guias para agentes AI
```

---

## Entidades de Domínio Principais

| Entidade      | Descrição                                      |
|---------------|-------------------------------------------------|
| Family        | Unidade familiar (tenant)                       |
| User          | Membro da família                               |
| Account       | Conta bancária ou carteira                      |
| Transaction   | Movimento financeiro (receita ou despesa)        |
| Category      | Classificação de transações                     |
| Budget        | Orçamento mensal por categoria                  |
| SavingsGoal   | Meta de poupança                                |
| Debt          | Dívida com parcelas                             |
| Asset         | Bem patrimonial                                 |
| FinancialRule | Regra automática (dízimo, INSS, rendimento)     |

---

## Convenções

- **Idioma do código**: Inglês
- **Idioma da UI**: Português (pt_BR)
- **Idioma da documentação**: Português (com termos técnicos em inglês)
- **Moeda padrão**: BRL (R$)
- **Timezone**: America/Sao_Paulo
- **Formato de data**: dd/mm/yyyy

---

## Documentos Relacionados

| Documento | Caminho | Descrição |
|-----------|---------|-----------|
| Agent Guide | `docs/agents/AGENT_GUIDE.md` | Regras para agentes AI |
| Product Spec | `docs/product/PRODUCT_SPEC.md` | Especificação do produto |
| Financial Rules | `docs/finance/FINANCIAL_RULES.md` | Regras financeiras |
| Domain Model | `docs/architecture/DOMAIN_MODEL.md` | Modelo de domínio |
| ERD | `docs/architecture/ERD.md` | Diagrama de entidades |
| Architecture | `docs/architecture/ARCHITECTURE.md` | Arquitetura do sistema |
| Development Guide | `docs/development/DEVELOPMENT_GUIDE.md` | Guia de desenvolvimento |

---

## Estado Atual

**Fase**: Fundação (Setup inicial)
**Versão**: 0.1.0
**Branch**: main

---

*Última atualização: 2026-09-29*
