# FAMFIN — Deployment Guide

## Ambientes

| Ambiente | Uso | URL |
|----------|-----|-----|
| Local | Desenvolvimento | `http://localhost:8000` |
| Produção | Uso real | TBD |

---

## Deploy Local

```bash
php artisan serve
npm run dev
```

---

## Deploy Produção (Futuro)

```bash
# Build assets
npm run build

# Otimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Migrations
php artisan migrate --force
```

---

## Checklist de Deploy

- [ ] Testes passam
- [ ] `.env` configurado
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] HTTPS configurado
- [ ] Migrations executadas
- [ ] Assets compilados
- [ ] Cache otimizado

---

*Última atualização: 2026-09-29*
