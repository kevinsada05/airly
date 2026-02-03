<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_scans', function (Blueprint $table) {
            $table->foreignId('image_upload_id')->nullable()->after('user_id')->constrained('image_uploads')->nullOnDelete();
            $table->index('image_upload_id');
        });
    }

    public function down(): void
    {
        Schema::table('waste_scans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_upload_id');
        });
    }
};
