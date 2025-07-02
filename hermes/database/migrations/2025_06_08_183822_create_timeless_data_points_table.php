<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timeless_data_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('node_id');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeless_data_points');
    }
};
