<?php

use App\Models\Core\PermissionCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('label')->nullable()->after('name');
            $table->foreignId('permission_category_id')
                ->nullable()
                ->after('label')
                ->constrained('permission_categories')
                ->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_category_id']);

            $table->dropColumn(['permission_category_id', 'label']);

            $table->dropSoftDeletes();
        });
    }
};
