<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('flo_id');
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_in_experiment_id')->nullable();
            $table->foreignId('filler_in_experiment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flo_pages');
    }
};
