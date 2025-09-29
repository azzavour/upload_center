<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('track_id')->unique();
            $table->string('track_name');
            $table->string('artist_id');
            $table->string('artist_name');
            $table->string('album_name')->nullable();
            $table->string('genre')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('track_price', 10, 2)->nullable();
            $table->decimal('collection_price', 10, 2)->nullable();
            $table->string('country', 10)->nullable();
            $table->foreignId('upload_history_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracks');
    }
};