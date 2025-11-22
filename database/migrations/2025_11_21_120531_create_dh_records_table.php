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
        Schema::create('dh_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')
              ->constrained('dh_folders')
              ->cascadeOnDelete();

            $table->date('doc_date')->nullable();     // "Date" column in table
            $table->text('description')->nullable();  // Description textarea

            // Single attachment per row for now
            $table->string('file_path')->nullable();  // storage path
            $table->string('original_name')->nullable(); // nice name for download
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dh_records');
    }
};
