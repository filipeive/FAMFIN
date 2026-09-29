<?php

/**
 * FAMFIN Money Helpers
 *
 * All monetary values are stored as integers (centavos).
 * These helpers handle formatting and parsing.
 */

if (!function_exists('formatMoney')) {
    /**
     * Format centavos to a human-readable money string.
     *
     * @param int $centavos Value in centavos
     * @param string $currency Currency code (default: MZN)
     * @return string Formatted string like "53.418,75 MZN"
     */
    function formatMoney(int $centavos, string $currency = 'MZN'): string
    {
        $value = $centavos / 100;

        return number_format($value, 2, ',', '.') . ' ' . $currency;
    }
}

if (!function_exists('parseMoney')) {
    /**
     * Parse a human-readable money string to centavos.
     *
     * Handles formats like:
     * - "53418.75"
     * - "53.418,75"
     * - "53 418,75 MZN"
     *
     * @param string|float|int $value The value to parse
     * @return int Value in centavos
     */
    function parseMoney(string|float|int $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value * 100);
        }

        // Remove currency symbols and spaces
        $clean = preg_replace('/[A-Z\s]/', '', $value);

        // Handle "53.418,75" format (dot as thousands, comma as decimal)
        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d{1,2})?$/', $clean)) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }
        // Handle "53,418.75" format (comma as thousands, dot as decimal)
        elseif (preg_match('/^\d{1,3}(,\d{3})*(\.\d{1,2})?$/', $clean)) {
            $clean = str_replace(',', '', $clean);
        }
        // Handle "53418,75" format (comma as decimal, no thousands)
        elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return (int) round((float) $clean * 100);
    }
}

if (!function_exists('moneyToCents')) {
    /**
     * Alias for parseMoney. Convert a decimal value to centavos.
     */
    function moneyToCents(float $value): int
    {
        return (int) round($value * 100);
    }
}

if (!function_exists('centsToMoney')) {
    /**
     * Convert centavos to decimal value.
     */
    function centsToMoney(int $centavos): float
    {
        return $centavos / 100;
    }
}
