<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_spreadsheet_successfully(): void
    {
        $filePath = base_path('filipe e ivete(Updated).xlsx');
        if (!file_exists($filePath)) {
            $this->markTestSkipped('Spreadsheet file not found for test.');
        }

        $family = Family::create([
            'name' => 'Família dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $user = User::factory()->create([
            'family_id' => $family->id,
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.app',
        ]);

        $account = Account::create([
            'family_id' => $family->id,
            'name' => 'Conta Corrente Principal',
            'type' => \App\Enums\AccountType::Checking,
            'initial_balance' => 0,
            'balance' => 0,
        ]);

        $service = app(ExcelImportService::class);
        $stats = $service->import($family, $filePath, $user, $account);

        $this->assertGreaterThanOrEqual(50, $stats['months_processed']);
        $this->assertGreaterThan(500, $stats['transactions_created']);
        $this->assertGreaterThan(100, $stats['budgets_created']);

        $this->assertDatabaseHas('categories', [
            'family_id' => $family->id,
            'name' => 'Salário',
        ]);

        $this->assertDatabaseHas('categories', [
            'family_id' => $family->id,
            'name' => 'Despesas da Casa',
        ]);
    }
}
