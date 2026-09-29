# FAMFIN — Development Guide

## Setup Inicial

### Requisitos
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ (para Vite)

### Instalação

```bash
# Clone o repositório
git clone <repo-url> famfin
cd famfin

# Instalar dependências PHP
composer install

# Copiar .env
cp .env.example .env

# Gerar APP_KEY
php artisan key:generate

# Criar database
mysql -u root -p -e "CREATE DATABASE famfin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Executar migrations
php artisan migrate

# Executar seeders (dados de exemplo)
php artisan db:seed

# Instalar dependências JS
npm install

# Iniciar servidor de desenvolvimento
php artisan serve
npm run dev
```

---

## Convenções de Código

### Nomenclatura
- **Models**: PascalCase, singular (`Transaction`, `SavingsGoal`)
- **Tables**: snake_case, plural (`transactions`, `savings_goals`)
- **Controllers**: PascalCase + Controller (`TransactionController`)
- **Services**: PascalCase + Service (`TransactionService`)
- **Requests**: Store/Update + Model + Request (`StoreTransactionRequest`)
- **Enums**: PascalCase (`TransactionType`)
- **Views**: snake_case (`transactions/show.blade.php`)

### PHP
- PSR-12
- Laravel Pint para formatação
- Type hints em todos os métodos
- Return types explícitos

### Commits
- Formato: `type(scope): description`
- Tipos: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`
- Exemplos:
  - `feat(transactions): add recurring transactions`
  - `fix(budget): correct percentage calculation`
  - `docs(finance): update financial rules`

---

## Estrutura de Diretórios

```
app/
├── Enums/           ← Enums de domínio
├── Events/          ← Domain Events
├── Exceptions/      ← Custom Exceptions
├── Http/
│   ├── Controllers/ ← Thin Controllers
│   ├── Middleware/   ← Custom Middleware
│   └── Requests/    ← Form Requests
├── Listeners/       ← Event Listeners
├── Models/          ← Eloquent Models
├── Policies/        ← Authorization Policies
└── Services/        ← Domain Services
```

---

*Última atualização: 2026-09-29*
