<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_upload_id')->constrained()->cascadeOnDelete();
            $table->boolean('pollution_detected');
            $table->enum('severity', ['green', 'orange', 'red']);
            $table->decimal('confidence', 5, 4)->nullable();
            $table->json('raw_output')->nullable();
            $table->string('model_name')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('image_upload_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
