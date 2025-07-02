<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flo_page_node', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flo_page_id')
                ->constrained('flo_pages')
                ->cascadeOnDelete();
            $table->foreignId('node_id')
                ->constrained('nodes')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flo_page_node');
    }
};
