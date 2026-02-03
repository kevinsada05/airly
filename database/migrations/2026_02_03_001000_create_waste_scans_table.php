<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('item_type')->nullable();
            $table->boolean('recyclable')->nullable();
            $table->text('instructions')->nullable();
            $table->text('warnings')->nullable();
            $table->json('raw_output')->nullable();
            $table->string('model_name')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_scans');
    }
};
