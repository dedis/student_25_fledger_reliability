<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->string('target_page_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'target_page_id')) {
                $table->dropColumn('target_page_id');
            }
        });
    }
};
