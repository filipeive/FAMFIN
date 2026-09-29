# FAMFIN — Money Model

## Princípio

> **Todo valor monetário é um inteiro representando centavos.**

---

## Por quê?

```php
// ❌ ERRADO — Ponto flutuante
0.1 + 0.2 = 0.30000000000000004

// ✅ CORRETO — Inteiros (centavos)
10 + 20 = 30  // R$ 0,30
```

---

## Convenções

| Formato | Exemplo | Uso |
|---------|---------|-----|
| Database | `10050` | Armazenamento (integer) |
| Backend | `10050` | Cálculos (integer) |
| API | `10050` | Comunicação (integer) |
| Frontend | `R$ 100,50` | Exibição (formatted string) |

---

## Helpers

```php
// Conversão para exibição
function formatMoney(int $cents): string
{
    return 'R$ ' . number_format($cents / 100, 2, ',', '.');
}

// Conversão de input
function parseMoney(string $value): int
{
    $clean = str_replace(['R$', '.', ' '], '', $value);
    $clean = str_replace(',', '.', $clean);
    return (int) round((float) $clean * 100);
}
```

---

## Regras de Arredondamento

- Divisões: sempre `floor()` (arredondar para baixo)
- Multiplicações: `round()` com 0 casas decimais
- Diferença residual registrada como "ajuste de arredondamento"

---

## No Eloquent

```php
// Model com cast para centavos
protected $casts = [
    'amount' => 'integer',
];

// Accessor para exibição
public function getFormattedAmountAttribute(): string
{
    return formatMoney($this->amount);
}

// Mutator para input
public function setAmountFromInputAttribute(string $value): void
{
    $this->attributes['amount'] = parseMoney($value);
}
```

---

*Última atualização: 2026-09-29*
