# FAMFIN — Offline First

## Princípio

> O app mobile deve funcionar **sem internet**. Sincronizar quando possível.

---

## Estratégia

1. **Leitura**: Sempre do cache local (SQLite)
2. **Escrita**: Gravar localmente + enfileirar para sync
3. **Sync**: Quando há conexão, enviar fila e receber atualizações
4. **Conflito**: Last-write-wins com timestamp do servidor

---

## Dados Offline

| Dado | Disponível offline | Editável offline |
|------|--------------------|------------------|
| Dashboard | ✅ (cache) | ❌ |
| Transações | ✅ | ✅ (enfileirada) |
| Categorias | ✅ | ❌ |
| Contas | ✅ | ❌ |
| Saldos | ✅ (snapshot) | ❌ |
| Orçamentos | ✅ | ❌ |

---

## Fila de Sync

```json
{
    "id": "ulid",
    "action": "create",
    "entity": "transaction",
    "payload": { ... },
    "created_at": "2026-09-29T10:00:00Z",
    "synced_at": null,
    "status": "pending"
}
```

---

*Fase: Planejamento (v2.0)*
*Última atualização: 2026-09-29*
