<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('accounting_category_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('accounting_categories')
                ->nullOnDelete();

            $table->foreignId('accounting_subcategory_id')
                ->nullable()
                ->after('accounting_category_id')
                ->constrained('accounting_subcategories')
                ->nullOnDelete();

            $table->foreignId('accounting_account_id')
                ->nullable()
                ->after('accounting_subcategory_id')
                ->constrained('accounting_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accounting_account_id');
            $table->dropConstrainedForeignId('accounting_subcategory_id');
            $table->dropConstrainedForeignId('accounting_category_id');
        });
    }
};
