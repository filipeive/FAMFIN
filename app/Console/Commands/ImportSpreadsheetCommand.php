<?php

namespace App\Console\Commands;

use App\Models\Family;
use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportSpreadsheetCommand extends Command
{
    protected $signature = 'famfin:import-spreadsheet {file? : Path to the Excel spreadsheet file}';
    protected $description = 'Import financial historical data from Excel spreadsheet into FAMFIN';

    public function handle(ExcelImportService $importService): int
    {
        $filePath = $this->argument('file') ?? base_path('filipe e ivete(Updated).xlsx');

        if (!file_exists($filePath)) {
            $this->error("Spreadsheet file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $family = Family::first();
        if (!$family) {
            $this->error('No family found in database. Please run seeders first.');
            return Command::FAILURE;
        }

        $this->info("Importing spreadsheet for family: {$family->name}...");
        $this->info("File: {$filePath}");

        try {
            $stats = $importService->import($family, $filePath);

            $this->newLine();
            $this->info('✅ Import completed successfully!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Months Processed', $stats['months_processed']],
                    ['Transactions Created', $stats['transactions_created']],
                    ['Budgets Created', $stats['budgets_created']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
