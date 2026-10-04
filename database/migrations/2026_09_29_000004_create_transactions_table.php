<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('account_id');
            $table->ulid('category_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->ulid('transfer_account_id')->nullable();
            $table->string('type');
            $table->string('status')->default('completed');
            $table->bigInteger('amount');
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->ulid('parent_transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('transfer_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('parent_transaction_id')->references('id')->on('transactions')->nullOnDelete();

            $table->index('account_id');
            $table->index('category_id');
            $table->index('user_id');
            $table->index('transaction_date');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
