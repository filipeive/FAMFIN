<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialRuleController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Transactions
Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('index');
    Route::get('/create', [TransactionController::class, 'create'])->name('create');
    Route::post('/', [TransactionController::class, 'store'])->name('store');
    Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
});

// Budgets
Route::prefix('budgets')->name('budgets.')->group(function () {
    Route::get('/', [BudgetController::class, 'index'])->name('index');
    Route::post('/', [BudgetController::class, 'store'])->name('store');
    Route::post('/copy', [BudgetController::class, 'copy'])->name('copy');
});

// Savings Goals
Route::prefix('savings')->name('savings.')->group(function () {
    Route::get('/', [SavingsController::class, 'index'])->name('index');
    Route::post('/', [SavingsController::class, 'store'])->name('store');
    Route::post('/{goal}/contribute', [SavingsController::class, 'contribute'])->name('contribute');
});

// Debts
Route::prefix('debts')->name('debts.')->group(function () {
    Route::get('/', [DebtController::class, 'index'])->name('index');
    Route::post('/', [DebtController::class, 'store'])->name('store');
    Route::post('/{debt}/pay', [DebtController::class, 'pay'])->name('pay');
});

// Accounts
Route::prefix('accounts')->name('accounts.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::post('/', [AccountController::class, 'store'])->name('store');
    Route::post('/{account}/recalculate', [AccountController::class, 'recalculate'])->name('recalculate');
});

// Financial Rules
Route::prefix('rules')->name('rules.')->group(function () {
    Route::get('/', [FinancialRuleController::class, 'index'])->name('index');
    Route::put('/{rule}', [FinancialRuleController::class, 'update'])->name('update');
    Route::post('/{rule}/toggle', [FinancialRuleController::class, 'toggle'])->name('toggle');
});
