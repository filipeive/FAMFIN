# FAMFIN — Security

## Princípios de Segurança

### 1. Autenticação
- Laravel Sanctum para API tokens
- Breeze para web authentication
- Session-based para web, token-based para API/mobile

### 2. Autorização
- Policies para cada Model
- Tenant isolation (Family scope)
- Role-based access (admin, member, viewer)

### 3. Dados Financeiros
- Todos os dados financeiros são sensíveis
- Nunca expor dados de uma família para outra
- Logs de auditoria para transações

### 4. Validação
- Sempre validar via Form Requests
- Nunca confiar em input do frontend
- Sanitizar todos os inputs

### 5. HTTPS
- Obrigatório em produção
- `APP_URL` deve usar `https://`

---

## Tenant Isolation

```php
// Sempre filtrar por family_id
$transactions = Transaction::where('family_id', auth()->user()->family_id)->get();

// Usar Global Scopes
class FamilyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('family_id', auth()->user()->family_id);
    }
}
```

---

*Última atualização: 2026-09-29*
