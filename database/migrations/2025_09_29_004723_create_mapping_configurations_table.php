<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mapping_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excel_format_id')->constrained()->onDelete('cascade');
            $table->string('mapping_index')->unique();
            $table->json('column_mapping');
            $table->json('transformation_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mapping_configurations');
    }
};