<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialRuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Application Routes (Protected by Auth Middleware)
Route::middleware('auth')->group(function () {
    // Session & Profile
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // Core Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Transactions CRUD & Bulk Delete
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('bulk_delete');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });

    // Accounts CRUD
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::get('/{account}', [AccountController::class, 'show'])->name('show');
        Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('edit');
        Route::put('/{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
        Route::post('/recalculate', [AccountController::class, 'recalculate'])->name('recalculate');
        Route::post('/{account}/allowance', [AccountController::class, 'allocateAllowance'])->name('allocate_allowance');
    });

    // Categories CRUD & Subcategories Mass Delete
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/unlink', [CategoryController::class, 'unlink'])->name('unlink');
        Route::delete('/{category}/children', [CategoryController::class, 'destroyChildren'])->name('destroy_children');
    });

    // Budgets
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::post('/copy', [BudgetController::class, 'copy'])->name('copy');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
    });

    // Savings Goals & Contributions
    Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [SavingsController::class, 'index'])->name('index');
        Route::post('/', [SavingsController::class, 'store'])->name('store');
        Route::get('/{goal}/edit', [SavingsController::class, 'edit'])->name('edit');
        Route::put('/{goal}', [SavingsController::class, 'update'])->name('update');
        Route::delete('/{goal}', [SavingsController::class, 'destroy'])->name('destroy');
        Route::post('/{goal}/contribute', [SavingsController::class, 'contribute'])->name('contribute');
        Route::delete('/contributions/{contribution}', [SavingsController::class, 'destroyContribution'])->name('contributions.destroy');
    });

    // Debts & Payments
    Route::prefix('debts')->name('debts.')->group(function () {
        Route::get('/', [DebtController::class, 'index'])->name('index');
        Route::post('/', [DebtController::class, 'store'])->name('store');
        Route::get('/{debt}/edit', [DebtController::class, 'edit'])->name('edit');
        Route::put('/{debt}', [DebtController::class, 'update'])->name('update');
        Route::delete('/{debt}', [DebtController::class, 'destroy'])->name('destroy');
        Route::post('/{debt}/payment', [DebtController::class, 'payment'])->name('payment');
        Route::delete('/payments/{payment}', [DebtController::class, 'destroyPayment'])->name('payments.destroy');
    });

    // Assets & Net Worth CRUD
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
    });

    // Financial Rules & Deductions
    Route::prefix('rules')->name('rules.')->group(function () {
        Route::get('/', [FinancialRuleController::class, 'index'])->name('index');
        Route::post('/', [FinancialRuleController::class, 'store'])->name('store');
        Route::patch('/{rule}/toggle', [FinancialRuleController::class, 'toggle'])->name('toggle');
        Route::delete('/{rule}', [FinancialRuleController::class, 'destroy'])->name('destroy');
    });

    // Analytics & Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
});
