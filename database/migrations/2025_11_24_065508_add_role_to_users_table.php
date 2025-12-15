<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'admin', 'consultant', 'user'
            $table->string('role', 20)->default('user')->after('is_admin');
        });

        // Optional: backfill existing users based on is_admin
        DB::table('users')
            ->where('is_admin', true)
            ->update(['role' => 'admin']);

        DB::table('users')
            ->where('is_admin', false)
            ->update(['role' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
