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
        Schema::create('purchase_requestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('division_id')
                ->constrained('divisions')
                ->restrictOnDelete();

            $table->foreignId('requester_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('pr_number', 50)->unique();

            $table->date('request_date');
            $table->date('required_date')->nullable();

            $table->string('status', 30)->default('draft');

            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requestions');
    }
};
