<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('block_notebooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id');
            $table->unsignedBigInteger('notebook_id');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('block_id')
                  ->references('id')->on('blocks')
                  ->cascadeOnDelete();

            $table->foreign('notebook_id')
                  ->references('id')->on('notebooks')
                  ->cascadeOnDelete();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('block_notebooks');
    }
};
