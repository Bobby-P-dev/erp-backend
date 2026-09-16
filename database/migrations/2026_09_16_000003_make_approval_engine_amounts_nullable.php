<?php

declare(strict_types=1);

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
        Schema::table('approval_configurations', function (Blueprint $table): void {
            $table->decimal('min_amount', 15, 2)->nullable()->default(null)->change();
            $table->decimal('max_amount', 15, 2)->nullable()->default(null)->change();
        });

        Schema::table('approval_requests', function (Blueprint $table): void {
            $table->decimal('total_amount', 15, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table): void {
            $table->decimal('total_amount', 15, 2)->default(0.00)->nullable(false)->change();
        });

        Schema::table('approval_configurations', function (Blueprint $table): void {
            $table->decimal('min_amount', 15, 2)->default(0.00)->nullable(false)->change();
            $table->decimal('max_amount', 15, 2)->nullable()->change();
        });
    }
};
