<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MoneyHelperTest extends TestCase
{
    public function test_format_money_formats_centavos_with_currency(): void
    {
        $this->assertEquals('100,50 MZN', formatMoney(10050, 'MZN'));
        $this->assertEquals('53.418,75 MZN', formatMoney(5341875, 'MZN'));
        $this->assertEquals('0,00 MZN', formatMoney(0, 'MZN'));
        $this->assertEquals('1.000.000,00 MZN', formatMoney(100000000, 'MZN'));
    }

    public function test_parse_money_parses_various_string_and_numeric_formats(): void
    {
        $this->assertEquals(10050, parseMoney(10050));
        $this->assertEquals(10050, parseMoney(100.50));
        $this->assertEquals(5341875, parseMoney('53418.75'));
        $this->assertEquals(5341875, parseMoney('53.418,75'));
        $this->assertEquals(5341875, parseMoney('53 418,75 MZN'));
        $this->assertEquals(200000, parseMoney('2000'));
        $this->assertEquals(200000, parseMoney('2.000,00'));
    }

    public function test_money_to_cents_and_cents_to_money(): void
    {
        $this->assertEquals(5341875, moneyToCents(53418.75));
        $this->assertEquals(53418.75, centsToMoney(5341875));
        $this->assertEquals(0, moneyToCents(0.0));
        $this->assertEquals(0.0, centsToMoney(0));
    }
}
