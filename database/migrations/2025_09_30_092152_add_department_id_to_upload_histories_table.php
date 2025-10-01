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
        Schema::table('upload_histories', function (Blueprint $table) {
            // Add department_id if not exists
            if (!Schema::hasColumn('upload_histories', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('mapping_configuration_id')->constrained()->onDelete('cascade');
            }
            
            // Add upload_mode column to track if it's replace or append
            if (!Schema::hasColumn('upload_histories', 'upload_mode')) {
                $table->enum('upload_mode', ['replace', 'append'])->default('append')->after('department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_histories', function (Blueprint $table) {
            if (Schema::hasColumn('upload_histories', 'upload_mode')) {
                $table->dropColumn('upload_mode');
            }
            
            if (Schema::hasColumn('upload_histories', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
