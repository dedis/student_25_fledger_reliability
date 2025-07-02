<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->boolean('bookmarked')->default(false);
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            if (Schema::hasColumn('experiments', 'bookmarked')) {
                $table->dropColumn('bookmarked');
            }
            if (Schema::hasColumn('experiments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
