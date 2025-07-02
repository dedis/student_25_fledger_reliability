<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->text('target_pages')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'target_pages')) {
                $table->dropColumn('target_pages');
            }
        });
    }
};
