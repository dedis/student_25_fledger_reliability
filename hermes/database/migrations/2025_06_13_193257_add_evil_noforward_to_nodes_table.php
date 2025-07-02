<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->boolean('evil_noforward')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            if (Schema::hasColumn('nodes', 'evil_noforward')) {
                $table->dropColumn('evil_noforward');
            }
        });
    }
};
