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
        Schema::table('dh_folders', function (Blueprint $table) {
            $table->boolean('is_trashed')->default(false)->after('parent_id');
            $table->timestamp('trashed_at')->nullable()->after('is_trashed');
            $table->unsignedBigInteger('trashed_by')->nullable()->after('trashed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dh_folders', function (Blueprint $table) {
            $table->dropColumn(['is_trashed', 'trashed_at', 'trashed_by']);
        });
    }
};
