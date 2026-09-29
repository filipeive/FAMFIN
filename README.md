# FAMFIN — Family Finance

> Sistema de gestão financeira familiar construído em Laravel 13.

---

## Início Rápido

### Requisitos

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+

### Instalação

```bash
# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Criar database
mysql -u root -p -e "CREATE DATABASE famfin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Executar migrations
php artisan migrate

# Instalar assets
npm install
npm run dev

# Iniciar servidor
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

---

## Documentação

Toda a documentação está em `docs/`. Comece por [MASTER_CONTEXT.md](MASTER_CONTEXT.md).

| Área | Diretório |
|------|-----------|
| Produto | `docs/product/` |
| Finanças | `docs/finance/` |
| Arquitetura | `docs/architecture/` |
| Mobile | `docs/mobile/` |
| Desenvolvimento | `docs/development/` |
| Agentes AI | `docs/agents/` |

---

## Stack

- **Backend**: Laravel 13 (PHP)
- **Database**: MySQL
- **Frontend**: Blade + Vite
- **Mobile** (futuro): NativePHP

---

## Licença

Projeto privado. Todos os direitos reservados.
