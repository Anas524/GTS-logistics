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
        Schema::create('dh_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_name', 150);
            $table->string('month_label', 50)->nullable();   // e.g. "Nov 2025"
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dh_folders');
    }
};
