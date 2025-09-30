<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. CREATE DEPARTMENTS TABLE
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. ADD COLUMNS TO USERS TABLE
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('email')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin'])->default('user')->after('department_id');
            }
        });

        // 3. ADD COLUMNS TO EXCEL_FORMATS TABLE
        Schema::table('excel_formats', function (Blueprint $table) {
            if (!Schema::hasColumn('excel_formats', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('target_table')->constrained()->onDelete('cascade');
            }
        });

        // 4. ADD COLUMNS TO MAPPING_CONFIGURATIONS TABLE
        Schema::table('mapping_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('mapping_configurations', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('mapping_index')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('mapping_configurations', 'mapping_name')) {
                $table->string('mapping_name')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('mapping_configurations', 'description')) {
                $table->text('description')->nullable()->after('mapping_name');
            }
        });

        // 5. ADD COLUMNS TO UPLOAD_HISTORIES TABLE
        Schema::table('upload_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('upload_histories', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('mapping_configuration_id')->constrained()->onDelete('cascade');
            }
            
            // Check if uploaded_by exists and is not already a foreign key
            if (Schema::hasColumn('upload_histories', 'uploaded_by')) {
                // Try to add foreign key constraint if not exists
                $foreignKeys = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'upload_histories' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name LIKE '%uploaded_by%'
                ");
                
                if (empty($foreignKeys)) {
                    // Column exists but no foreign key, add it
                    $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
                }
            } else {
                // Column doesn't exist, create it with foreign key
                $table->foreignId('uploaded_by')->nullable()->after('uploaded_at')->constrained('users')->onDelete('set null');
            }
        });

        // 6. CREATE MASTER_DATA TABLE
        if (!Schema::hasTable('master_data')) {
            Schema::create('master_data', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained()->onDelete('cascade');
                $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
                $table->string('source_table');
                $table->json('data');
                $table->timestamps();
                
                $table->index(['department_id', 'source_table']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('master_data');
        
        Schema::table('upload_histories', function (Blueprint $table) {
            if (Schema::hasColumn('upload_histories', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            // Don't drop uploaded_by, it existed before
        });
        
        Schema::table('mapping_configurations', function (Blueprint $table) {
            $columns = ['department_id', 'mapping_name', 'description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('mapping_configurations', $column)) {
                    if ($column === 'department_id') {
                        $table->dropForeign(['department_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
        
        Schema::table('excel_formats', function (Blueprint $table) {
            if (Schema::hasColumn('excel_formats', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn(['department_id', 'role']);
            }
        });
        
        Schema::dropIfExists('departments');
    }
};