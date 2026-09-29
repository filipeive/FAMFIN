<?php

namespace App\Services;

use App\Enums\FinancialRuleType;
use App\Models\Family;
use App\Models\FinancialRule;

class FinancialRuleService
{
    /**
     * Calculate deductions for a given gross income according to family's active rules.
     * All monetary values are in centavos (integers).
     *
     * @param Family $family
     * @param int $grossAmount Gross income in centavos
     * @return array
     */
    public function calculateBreakdown(Family $family, int $grossAmount): array
    {
        $rules = $family->financialRules()
            ->where('is_active', true)
            ->where('apply_on_income', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $deductions = [];
        $totalDeductions = 0;
        $runningAmount = $grossAmount;

        // Process INSS first if present
        $inssRule = $rules->firstWhere('type', FinancialRuleType::Inss);
        if ($inssRule) {
            $inssAmount = (int) floor($grossAmount * ((float) $inssRule->percentage / 100));
            $deductions[] = [
                'rule_id' => $inssRule->id,
                'name' => $inssRule->name,
                'type' => $inssRule->type->value,
                'percentage' => (float) $inssRule->percentage,
                'amount' => $inssAmount,
                'base_amount' => $grossAmount,
            ];
            $totalDeductions += $inssAmount;
            $runningAmount -= $inssAmount;
        }

        // Process Tithe (Dízimo)
        $titheRule = $rules->firstWhere('type', FinancialRuleType::Tithe);
        if ($titheRule) {
            // Default tithe base is gross or net-after-inss depending on config
            $baseConfig = $titheRule->config['base'] ?? 'gross';
            $titheBase = ($baseConfig === 'net_after_inss') ? $runningAmount : $grossAmount;
            $titheAmount = (int) floor($titheBase * ((float) $titheRule->percentage / 100));

            $deductions[] = [
                'rule_id' => $titheRule->id,
                'name' => $titheRule->name,
                'type' => $titheRule->type->value,
                'percentage' => (float) $titheRule->percentage,
                'amount' => $titheAmount,
                'base_amount' => $titheBase,
            ];
            $totalDeductions += $titheAmount;
        }

        // Process other active custom rules
        $otherRules = $rules->filter(fn ($r) => !in_array($r->type, [FinancialRuleType::Inss, FinancialRuleType::Tithe, FinancialRuleType::Yield]));
        foreach ($otherRules as $rule) {
            $ruleAmount = (int) floor($grossAmount * ((float) $rule->percentage / 100));
            $deductions[] = [
                'rule_id' => $rule->id,
                'name' => $rule->name,
                'type' => $rule->type->value,
                'percentage' => (float) $rule->percentage,
                'amount' => $ruleAmount,
                'base_amount' => $grossAmount,
            ];
            $totalDeductions += $ruleAmount;
        }

        $netAmount = max(0, $grossAmount - $totalDeductions);

        return [
            'gross_amount' => $grossAmount,
            'deductions' => $deductions,
            'total_deductions' => $totalDeductions,
            'net_amount' => $netAmount,
            'formatted_gross' => formatMoney($grossAmount, $family->currency),
            'formatted_deductions' => formatMoney($totalDeductions, $family->currency),
            'formatted_net' => formatMoney($netAmount, $family->currency),
        ];
    }

    /**
     * Calculate yield on investment account balance.
     *
     * @param int $balance Account balance in centavos
     * @param float $annualRatePercentage Annual percentage rate (e.g. 15.0 for 15%)
     * @param int $days Number of days (default: 30 for monthly calculation, or 1 for daily)
     * @return int Yield in centavos
     */
    public function calculateYield(int $balance, float $annualRatePercentage, int $days = 30): int
    {
        if ($balance <= 0 || $annualRatePercentage <= 0) {
            return 0;
        }

        $dailyRate = ($annualRatePercentage / 100) / 365;
        return (int) floor($balance * $dailyRate * $days);
    }
}
