<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('apply_on_income')->default(true);
            $table->json('config')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_rules');
    }
};
