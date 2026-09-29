<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->bigInteger('current_value');
            $table->bigInteger('purchase_value')->nullable();
            $table->date('purchase_date')->nullable();
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
