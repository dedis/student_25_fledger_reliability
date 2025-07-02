<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->integer('filler_amount')->default(0)->after('name');
            $table->integer('target_amount')->default(0)->after('filler_amount');
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'filler_amount')) {
                $table->dropColumn('filler_amount');
            }
            if (Schema::hasColumn('experiments', 'target_amount')) {
                $table->dropColumn('target_amount');
            }
        });
    }
};
