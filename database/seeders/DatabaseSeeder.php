<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\FinancialRuleType;
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
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'timezone' => 'Africa/Maputo',
            'locale' => 'pt_PT',
        ]);

        // ── Create default users ───────────────────
        User::create([
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.app',
            'password' => Hash::make('password'),
            'family_id' => $family->id,
            'role' => UserRole::Admin,
        ]);

        User::create([
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.mz',
            'password' => Hash::make('password'),
            'family_id' => $family->id,
            'role' => UserRole::Admin,
        ]);

        // ── Create default account with 0 balance ──
        Account::create([
            'family_id' => $family->id,
            'name' => 'Conta Principal',
            'type' => AccountType::Checking,
            'balance' => 0,
            'initial_balance' => 0,
            'color' => '#3B82F6',
            'icon' => '🏦',
        ]);

        // ── Seed all 54 Categories from categories.json ──
        $jsonPath = database_path('seeders/categories.json');
        if (file_exists($jsonPath)) {
            $categories = json_decode(file_get_contents($jsonPath), true);
            $parentMap = [];

            // 1st pass: create top-level categories
            foreach ($categories as $cat) {
                if (empty($cat['parent_name'])) {
                    $created = Category::create([
                        'family_id' => $family->id,
                        'name' => $cat['name'],
                        'type' => $cat['type'],
                        'icon' => $cat['icon'],
                        'color' => $cat['color'],
                        'sort_order' => $cat['sort_order'] ?? 0,
                        'is_system' => $cat['is_system'] ?? false,
                    ]);
                    $parentMap[$cat['name']] = $created->id;
                }
            }

            // 2nd pass: create subcategories linking to parent
            foreach ($categories as $cat) {
                if (!empty($cat['parent_name'])) {
                    $parentId = $parentMap[$cat['parent_name']] ?? null;
                    Category::create([
                        'family_id' => $family->id,
                        'parent_id' => $parentId,
                        'name' => $cat['name'],
                        'type' => $cat['type'],
                        'icon' => $cat['icon'],
                        'color' => $cat['color'],
                        'sort_order' => $cat['sort_order'] ?? 0,
                        'is_system' => $cat['is_system'] ?? false,
                    ]);
                }
            }
        }

        // ── Create financial rules ─────────────────
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
}
