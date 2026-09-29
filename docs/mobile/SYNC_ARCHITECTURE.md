# FAMFIN — Sync Architecture

## Visão Geral

Sincronização bidirectional entre mobile (SQLite) e servidor (MySQL).

---

## Fluxo

```
Mobile → Enviar transações pendentes → API → MySQL
                                         ↓
MySQL → API → Enviar atualizações → Mobile → SQLite
```

---

## Estratégia de Conflito

- **Last-write-wins** baseado em `updated_at` do servidor
- Servidor é sempre o **source of truth**
- Transações criadas offline recebem `synced_at` quando confirmadas

---

## Endpoints de Sync

| Endpoint | Descrição |
|----------|-----------|
| `POST /api/v1/sync/push` | Enviar mudanças locais |
| `GET /api/v1/sync/pull?since={timestamp}` | Receber mudanças desde timestamp |
| `GET /api/v1/sync/status` | Verificar estado da sincronização |

---

## Regras

1. Sync apenas quando há conexão
2. Retry com backoff exponencial
3. Notificar usuário se houver conflitos não resolvíveis
4. Manter log de sync para debug

---

*Fase: Planejamento (v2.0)*
*Última atualização: 2026-09-29*
