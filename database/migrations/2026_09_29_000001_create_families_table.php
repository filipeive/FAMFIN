<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('currency', 3)->default('MZN');
            $table->string('timezone')->default('Africa/Maputo');
            $table->string('locale')->default('pt_PT');
            $table->timestamps();
        });

        // Add family_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('family_id')->nullable()->after('id');
            $table->string('role')->default('admin')->after('email');

            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropColumn(['family_id', 'role']);
        });

        Schema::dropIfExists('families');
    }
};
