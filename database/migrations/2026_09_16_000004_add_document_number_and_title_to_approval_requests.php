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
        Schema::table('approval_requests', function (Blueprint $table): void {
            $table->string('document_number', 100)->nullable()->after('approvable_id')->index('idx_appr_req_doc_number');
            $table->string('document_title', 255)->nullable()->after('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table): void {
            $table->dropIndex('idx_appr_req_doc_number');
            $table->dropColumn(['document_number', 'document_title']);
        });
    }
};
