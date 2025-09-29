<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upload_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excel_format_id')->constrained();
            $table->foreignId('mapping_configuration_id')->nullable()->constrained();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('error_details')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
            $table->timestamp('uploaded_at');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('upload_histories');
    }
};