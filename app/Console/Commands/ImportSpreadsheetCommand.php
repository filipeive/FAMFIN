<?php

namespace App\Console\Commands;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Family;
use App\Models\User;
use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportSpreadsheetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'famfin:import-spreadsheet {file? : Path to the excel spreadsheet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import 57-month historical financial data from Excel spreadsheet into FAMFIN';

    /**
     * Execute the console command.
     */
    public function handle(ExcelImportService $importService): int
    {
        $filePath = $this->argument('file') ?? base_path('filipe e ivete(Updated).xlsx');

        if (!file_exists($filePath)) {
            $this->error("Spreadsheet file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Starting import from: {$filePath}");

        $family = Family::firstOrCreate(
            ['name' => 'Família Filipe dos Santos'],
            [
                'currency' => 'MZN',
                'locale' => 'pt_MZ',
                'timezone' => 'Africa/Maputo',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'filipe@famfin.mz'],
            [
                'name' => 'Filipe dos Santos',
                'password' => bcrypt('password'),
            ]
        );

        $account = Account::firstOrCreate(
            ['family_id' => $family->id, 'name' => 'Conta Principal (Millennium BIM)'],
            [
                'type' => AccountType::Checking,
                'currency' => 'MZN',
                'initial_balance' => 0,
                'balance' => 0,
                'bank_name' => 'Millennium BIM',
                'include_in_total' => true,
                'is_active' => true,
            ]
        );

        $this->info("Importing months and calculating balances...");

        $stats = $importService->import($family, $filePath, $user, $account);

        $this->info("✅ Import completed successfully!");
        $this->table(
            ['Métrica', 'Total Importado'],
            [
                ['Meses Processados', $stats['months_processed']],
                ['Transações Criadas', $stats['transactions_created']],
                ['Orçamentos Definidos', $stats['budgets_created']],
                ['Saldo Final Calculado', formatMoney($account->fresh()->balance, 'MZN')],
            ]
        );

        return Command::SUCCESS;
    }
}
