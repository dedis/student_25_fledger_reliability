<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id');
            $table->string('name');
            $table->string('value');
            $table->timestamp('time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_points');
    }
};
