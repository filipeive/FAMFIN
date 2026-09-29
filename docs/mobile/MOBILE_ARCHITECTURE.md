# FAMFIN — Mobile Architecture

## Visão Geral

A aplicação mobile do FAMFIN será construída com **NativePHP**, reutilizando o backend Laravel existente.

---

## Princípio Fundamental

> **Não duplicar Financial Domain para NativePHP.**
> A lógica de negócio vive nos Services do Laravel. O mobile consome via API.

---

## Stack Mobile

| Componente | Tecnologia |
|-----------|------------|
| Framework | NativePHP |
| Backend | Laravel (existente) |
| Storage local | SQLite |
| Sync | API REST + Queue |
| UI | Blade + Vite (webview) |

---

## Arquitetura

```
┌──────────────────────┐
│   Mobile App          │
│   (NativePHP)         │
├──────────────────────┤
│   Local SQLite DB     │
│   (cache offline)     │
├──────────────────────┤
│   Sync Engine         │
│   (bidirectional)     │
├──────────────────────┤
│   REST API            │
│   (Laravel Backend)   │
├──────────────────────┤
│   MySQL               │
│   (source of truth)   │
└──────────────────────┘
```

---

## Regras

1. **Nunca** duplicar cálculos financeiros no mobile
2. Mobile **lê** dados do cache local
3. Mobile **envia** transações para API
4. Sync engine resolve conflitos
5. MySQL é sempre o **source of truth**

---

*Fase: Planejamento (v2.0)*
*Última atualização: 2026-09-29*
