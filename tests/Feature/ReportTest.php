<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected Family $family;
    protected Account $account;
    protected Category $incomeCategory;
    protected Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Millennium BIM',
            'type' => AccountType::Checking,
            'currency' => 'MZN',
            'initial_balance' => 500000,
            'balance' => 500000,
            'is_active' => true,
        ]);

        $this->incomeCategory = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Salário & Honorários',
            'type' => CategoryType::Income,
            'color' => '#10B981',
        ]);

        $this->expenseCategory = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Supermercado & Alimentação',
            'type' => CategoryType::Expense,
            'color' => '#EF4444',
        ]);
    }

    public function test_reports_screen_renders_successfully(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'type' => TransactionType::Income,
            'amount' => 10000000, // 100,000 MZN
            'description' => 'Salário Mensal',
            'transaction_date' => '2026-09-01',
        ]);

        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'type' => TransactionType::Expense,
            'amount' => 2500000, // 25,000 MZN
            'description' => 'Compras Supermercado',
            'transaction_date' => '2026-09-05',
        ]);

        $response = $this->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Analytics');
        $response->assertSee('Fluxo de Caixa');
        $response->assertSee('Evolução Mensal');
        $response->assertSee('Supermercado');
        $response->assertSee('Exportar CSV');
    }

    public function test_reports_can_filter_by_month(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'type' => TransactionType::Expense,
            'amount' => 1500000,
            'description' => 'Gasto Setembro',
            'transaction_date' => '2026-09-10',
        ]);

        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'type' => TransactionType::Expense,
            'amount' => 3000000,
            'description' => 'Gasto Agosto',
            'transaction_date' => '2026-08-10',
        ]);

        $response = $this->get(route('reports.index', ['month' => '2026-09']));

        $response->assertStatus(200);
        $response->assertSee('Total gasto no período selecionado: <strong class="text-slate-200">15.000,00 MZN</strong>', false);
        $response->assertSee('<option value="2026-09" selected>2026-09</option>', false);
    }

    public function test_reports_export_streams_csv_with_proper_headers_and_data(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'type' => TransactionType::Expense,
            'amount' => 125050, // 1,250.50 MZN
            'description' => 'Jantar Especial Restaurante',
            'transaction_date' => '2026-09-12',
            'notes' => 'Comemoração',
        ]);

        $response = $this->get(route('reports.export', ['month' => '2026-09']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('transacoes_2026-09.csv', (string) $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('ID,Data,Tipo,Conta,Destino,Categoria,Descrição', $content);
        $this->assertStringContainsString('Jantar Especial Restaurante', $content);
        $this->assertStringContainsString('1.250,50', $content);
    }

    public function test_reports_export_streams_all_history_when_month_is_omitted(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'type' => TransactionType::Income,
            'amount' => 5000000,
            'description' => 'Freelance Design',
            'transaction_date' => '2026-07-20',
        ]);

        $response = $this->get(route('reports.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('transacoes_historico.csv', (string) $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Freelance Design', $content);
    }
}
