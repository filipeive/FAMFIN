<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Receita',
            self::Expense => 'Despesa',
            self::Transfer => 'Transferência',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Income => '↑',
            self::Expense => '↓',
            self::Transfer => '↔',
        };
    }
}
