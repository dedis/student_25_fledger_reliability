<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->integer('targets_per_node')
                ->default(2)
                ->after('target_pages');
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'targets_per_node')) {
                $table->dropColumn('targets_per_node');
            }
        });
    }
};
