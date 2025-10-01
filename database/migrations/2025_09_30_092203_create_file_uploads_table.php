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
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('target_table'); // The actual table where data was inserted (e.g., dept_finance_tracks)
            $table->string('format_name'); // Format name for easy reference
            $table->integer('rows_inserted')->default(0);
            $table->enum('upload_mode', ['replace', 'append'])->default('append');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['department_id', 'uploaded_at']);
            $table->index(['uploaded_by', 'uploaded_at']);
            $table->index('target_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
