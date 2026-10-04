<?php

namespace App\Services;

use App\Models\Family;

class NotificationService
{
    public function __construct(
        protected BudgetService $budgetService,
        protected DebtService $debtService
    ) {}

    /**
     * Check and return active notifications/alerts for a family.
     */
    public function getActiveAlerts(Family $family, string $month): array
    {
        return $this->getPendingAlerts($family, $month);
    }

    /**
     * Check and return pending notifications/alerts for a family.
     */
    public function getPendingAlerts(Family $family, string $month): array
    {
        $alerts = [];

        // 1. Check budget alerts
        $budgetOverview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $month);
        foreach ($budgetOverview['items'] as $item) {
            if ($item['status'] === 'exceeded') {
                $alerts[] = [
                    'type' => 'budget_exceeded',
                    'title' => "Orçamento Excedido: {$item['category_name']}",
                    'message' => "Gastou {$item['formatted_spent']} de um teto de {$item['formatted_budget']}.",
                    'severity' => 'danger',
                ];
            } elseif ($item['status'] === 'warning') {
                $alerts[] = [
                    'type' => 'budget_warning',
                    'title' => "Alerta de Limite: {$item['category_name']}",
                    'message' => "Já consumiu {$item['percentage_used']}% do orçamento definido.",
                    'severity' => 'warning',
                ];
            }
        }

        // 2. Check overdue debts
        $debtSummary = $this->debtService->getFamilyDebtSummary($family);
        if (($debtSummary['overdue_count'] ?? 0) > 0) {
            $alerts[] = [
                'type' => 'debt_overdue',
                'title' => 'Dívidas em Atraso',
                'message' => "Existem {$debtSummary['overdue_count']} dívidas com pagamentos pendentes.",
                'severity' => 'danger',
            ];
        }

        return $alerts;
    }
}
