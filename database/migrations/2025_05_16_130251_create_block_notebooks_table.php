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
        Schema::create('block_notebook', function (Blueprint $table) {
    $table->foreignId('block_id')->constrained()->cascadeOnDelete();
    $table->foreignId('notebook_id')->constrained()->cascadeOnDelete();
    $table->primary(['block_id', 'notebook_id']);
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
