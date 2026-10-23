<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->ulid('parent_id')->nullable()->after('family_id');
            $table->boolean('is_allowance')->default(false)->after('include_in_total');
            $table->string('assigned_to_name')->nullable()->after('is_allowance');
            $table->bigInteger('allowance_monthly_amount')->nullable()->after('assigned_to_name');

            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
            $table->index('parent_id');
            $table->index('is_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'is_allowance',
                'assigned_to_name',
                'allowance_monthly_amount',
            ]);
        });
    }
};
