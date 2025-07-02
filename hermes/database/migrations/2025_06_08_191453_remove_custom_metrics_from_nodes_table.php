<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            if (Schema::hasColumn('nodes', 'amount_flo_value_sent')) {
                $table->dropColumn('amount_flo_value_sent');
            }
            if (Schema::hasColumn('nodes', 'amount_request_flo_metas_received')) {
                $table->dropColumn('amount_request_flo_metas_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->integer('amount_flo_value_sent')->default(0);
            $table->integer('amount_request_flo_metas_received')->default(0);
        });
    }
};
