<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('excel_formats', function (Blueprint $table) {
            $table->id();
            $table->string('format_name')->unique();
            $table->string('format_code')->unique();
            $table->text('description')->nullable();
            $table->json('expected_columns');
            $table->string('target_table');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('excel_formats');
    }
};