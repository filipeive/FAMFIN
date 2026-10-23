<?php

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Investment = 'investment';
    case Wallet = 'wallet';
    case CreditCard = 'credit_card';
    case MobileMoney = 'mobile_money';
    case SubAccount = 'sub_account';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta Corrente',
            self::Savings => 'Poupança',
            self::Investment => 'Investimento',
            self::Wallet => 'Carteira',
            self::CreditCard => 'Cartão de Crédito',
            self::MobileMoney => 'Mobile Money (M-Pesa/e-Mola)',
            self::SubAccount => 'Subconta / Mesada',
        };
    }
}
