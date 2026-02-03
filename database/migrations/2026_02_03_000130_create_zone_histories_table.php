<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->enum('severity', ['green', 'orange', 'red']);
            $table->timestamp('computed_at');
            $table->unsignedInteger('image_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_histories');
    }
};
