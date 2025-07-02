<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->dateTime('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'ended_at')) {
                $table->dropColumn('ended_at');
            }
        });
    }
};
