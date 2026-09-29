<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Enums\FinancialRuleType;
use App\Enums\AccountType;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create the family ──────────────────────
        $family = Family::create([
            'name' => 'Família dos Santos',
            'currency' => 'MZN',
            'timezone' => 'Africa/Maputo',
            'locale' => 'pt_PT',
        ]);

        // ── Create the admin user ──────────────────
        User::create([
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.app',
            'password' => Hash::make('password'),
            'family_id' => $family->id,
            'role' => UserRole::Admin,
        ]);

        // ── Create default account ─────────────────
        Account::create([
            'family_id' => $family->id,
            'name' => 'Conta Principal',
            'type' => AccountType::Checking,
            'balance' => 0,
            'initial_balance' => 0,
            'color' => '#3B82F6',
            'icon' => '🏦',
        ]);

        // ── Create income categories ───────────────
        $this->createCategories($family->id, CategoryType::Income, [
            ['name' => 'Salário', 'icon' => '💰', 'color' => '#10B981', 'is_system' => true],
            ['name' => 'Rendimentos', 'icon' => '📈', 'color' => '#059669'],
            ['name' => 'Extras', 'icon' => '🎁', 'color' => '#34D399'],
            ['name' => 'Reembolsos', 'icon' => '🔄', 'color' => '#6EE7B7'],
        ]);

        // ── Create expense categories ──────────────
        // Based on the spreadsheet structure: Casa, Família, Dívidas

        // Casa (Despesas Domésticas) — from the spreadsheet
        $casa = Category::create([
            'family_id' => $family->id,
            'name' => 'Casa',
            'type' => CategoryType::Expense,
            'icon' => '🏠',
            'color' => '#EF4444',
            'sort_order' => 1,
            'is_system' => true,
        ]);

        $this->createSubcategories($family->id, $casa->id, [
            ['name' => 'Rancho (Alimentação)', 'icon' => '🛒', 'color' => '#F87171'],
            ['name' => 'Transporte', 'icon' => '🚌', 'color' => '#FB923C'],
            ['name' => 'TV / Televisão', 'icon' => '📺', 'color' => '#A78BFA'],
            ['name' => 'Saúde', 'icon' => '🏥', 'color' => '#F43F5E'],
            ['name' => 'Água', 'icon' => '💧', 'color' => '#38BDF8'],
            ['name' => 'Electricidade', 'icon' => '⚡', 'color' => '#FBBF24'],
            ['name' => 'Mesadas', 'icon' => '👤', 'color' => '#C084FC'],
            ['name' => 'Educação', 'icon' => '📚', 'color' => '#2DD4BF'],
            ['name' => 'Vestuário', 'icon' => '👕', 'color' => '#FB7185'],
        ]);

        // Família (Apoio Familiar) — core concept from the spreadsheet
        $familia = Category::create([
            'family_id' => $family->id,
            'name' => 'Família / Apoio Familiar',
            'type' => CategoryType::Expense,
            'icon' => '👨‍👩‍👧‍👦',
            'color' => '#8B5CF6',
            'sort_order' => 2,
            'is_system' => true,
        ]);

        // Other expense categories
        $this->createCategories($family->id, CategoryType::Expense, [
            ['name' => 'Lazer', 'icon' => '🎉', 'color' => '#EC4899', 'sort_order' => 3],
            ['name' => 'Presentes', 'icon' => '🎁', 'color' => '#F472B6', 'sort_order' => 4],
            ['name' => 'Eventos', 'icon' => '🎊', 'color' => '#A855F7', 'sort_order' => 5],
            ['name' => 'Igreja / Religião', 'icon' => '⛪', 'color' => '#6366F1', 'sort_order' => 6],
            ['name' => 'Outros', 'icon' => '📦', 'color' => '#94A3B8', 'sort_order' => 99],
        ]);

        // ── Create financial rules ─────────────────
        // INSS Moçambique: 3% contribuição do trabalhador
        FinancialRule::create([
            'family_id' => $family->id,
            'type' => FinancialRuleType::Inss,
            'name' => 'INSS',
            'description' => 'Contribuição obrigatória para a segurança social — 3% do salário bruto',
            'percentage' => 3.00,
            'is_active' => true,
            'apply_on_income' => true,
            'sort_order' => 1,
        ]);

        // Dízimo: 10% — configuração pessoal do Filipe
        FinancialRule::create([
            'family_id' => $family->id,
            'type' => FinancialRuleType::Tithe,
            'name' => 'Dízimo',
            'description' => 'Contribuição religiosa — 10% do salário bruto',
            'percentage' => 10.00,
            'is_active' => true,
            'apply_on_income' => true,
            'sort_order' => 2,
        ]);
    }

    /**
     * Create top-level categories.
     */
    private function createCategories(string $familyId, CategoryType $type, array $categories): void
    {
        foreach ($categories as $i => $cat) {
            Category::create([
                'family_id' => $familyId,
                'name' => $cat['name'],
                'type' => $type,
                'icon' => $cat['icon'] ?? null,
                'color' => $cat['color'] ?? null,
                'sort_order' => $cat['sort_order'] ?? $i,
                'is_system' => $cat['is_system'] ?? false,
            ]);
        }
    }

    /**
     * Create subcategories under a parent.
     */
    private function createSubcategories(string $familyId, string $parentId, array $categories): void
    {
        foreach ($categories as $i => $cat) {
            Category::create([
                'family_id' => $familyId,
                'parent_id' => $parentId,
                'name' => $cat['name'],
                'type' => CategoryType::Expense,
                'icon' => $cat['icon'] ?? null,
                'color' => $cat['color'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}
