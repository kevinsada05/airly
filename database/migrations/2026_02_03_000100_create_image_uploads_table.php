<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamp('captured_at')->nullable();
            $table->string('file_path');
            $table->enum('source', ['drone', 'user']);
            $table->unsignedInteger('location_accuracy')->nullable();
            $table->enum('status', ['pending', 'processing', 'processed', 'failed']);
            $table->string('analysis_version')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_uploads');
    }
};
