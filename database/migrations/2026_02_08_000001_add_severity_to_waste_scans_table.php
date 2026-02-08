<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_scans', function (Blueprint $table) {
            $table->string('severity')->nullable()->after('item_type');
        });
    }

    public function down(): void
    {
        Schema::table('waste_scans', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
