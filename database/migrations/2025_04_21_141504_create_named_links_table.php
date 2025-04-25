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
Schema::create('named_links', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workbook_id')->constrained()->onDelete('cascade');
    $table->string('name'); // Иванов Иван 5Б
    $table->string('slug')->unique();
    $table->boolean('active')->default(true);
    $table->timestamp('open_at')->nullable();
    $table->timestamp('close_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('named_links');
    }
};
