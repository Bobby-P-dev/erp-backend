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
        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('supplier_item_code')->nullable();
            $table->string('supplier_item_name')->nullable();
            $table->text('reference_url')->nullable();
            $table->decimal('default_price', 18, 2)->nullable();
            $table->string('currency')->nullable();
            $table->decimal('minimum_order_quantity', 18, 4)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique([
                'supplier_id',
                'item_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_items');
    }
};
