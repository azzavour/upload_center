<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('mapping_configurations', function (Blueprint $table) {
        $table->string('mapping_name')->nullable()->after('mapping_index');
        $table->text('description')->nullable()->after('mapping_name');
    });
}

public function down()
{
    Schema::table('mapping_configurations', function (Blueprint $table) {
        $table->dropColumn(['mapping_name', 'description']);
    });
}
};
