<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->string('person_name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->bigInteger('original_amount');
            $table->bigInteger('remaining_amount');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->string('interest_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
            $table->index('status');
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('debt_id');
            $table->foreignId('user_id')->nullable();
            $table->bigInteger('amount');
            $table->date('payment_date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('debt_id')->references('id')->on('debts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('debt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
