<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tambah kolom department_id dan role ke users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('role', ['user', 'admin'])->default('user');
        });

        // Tambah department_id ke excel_formats
        Schema::table('excel_formats', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
        });

        // Tambah department_id dan metadata ke mapping_configurations
        Schema::table('mapping_configurations', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('mapping_name')->nullable(); // Nama mapping yang dibuat user
            $table->text('description')->nullable();
        });

        // Tambah department_id ke upload_history
        Schema::table('upload_histories', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // Tabel master untuk agregasi data
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
            $table->string('source_table'); // Tabel asal data
            $table->json('data'); // Data dalam format JSON
            $table->timestamps();
            
            $table->index(['department_id', 'source_table']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_data');
        
        Schema::table('upload_histories', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['department_id', 'uploaded_by']);
        });
        
        Schema::table('mapping_configurations', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'mapping_name', 'description']);
        });
        
        Schema::table('excel_formats', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'role']);
        });
        
        Schema::dropIfExists('departments');
    }
};