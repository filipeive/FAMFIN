<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->ulid('category_id');
            $table->string('month', 7); // YYYY-MM
            $table->bigInteger('amount');
            $table->integer('alert_threshold')->default(80);
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['family_id', 'category_id', 'month']);
            $table->index('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
