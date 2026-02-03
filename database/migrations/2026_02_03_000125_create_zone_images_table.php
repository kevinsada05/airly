<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('image_upload_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['zone_id', 'image_upload_id']);
            $table->index('zone_id');
            $table->index('image_upload_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_images');
    }
};
