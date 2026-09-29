<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('family_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->bigInteger('target_amount');
            $table->bigInteger('current_amount')->default(0);
            $table->date('deadline')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
        });

        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('savings_goal_id');
            $table->foreignId('user_id')->nullable();
            $table->bigInteger('amount');
            $table->date('contribution_date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('savings_goal_id')->references('id')->on('savings_goals')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('savings_goal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
        Schema::dropIfExists('savings_goals');
    }
};
