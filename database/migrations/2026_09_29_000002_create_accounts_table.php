<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->string('name');
            $table->string('type');
            $table->bigInteger('balance')->default(0);
            $table->bigInteger('initial_balance')->default(0);
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_total')->default(true);
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
