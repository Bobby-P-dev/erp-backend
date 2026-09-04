<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->string('category', 50);
            $table->string('prefix', 20);
            $table->string('period', 20);
            $table->unsignedInteger('current_number')->default(0);
            $table->string('format', 100)
                ->default('{prefix}-{period}-{number}');
            $table->unsignedTinyInteger('number_length')->default(6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique([
                'company_id',
                'category',
                'period',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
