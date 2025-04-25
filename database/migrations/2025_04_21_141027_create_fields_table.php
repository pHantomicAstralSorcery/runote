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
Schema::create('fields', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workbook_id')->constrained()->onDelete('cascade');
    $table->string('label');
    $table->enum('type', ['text', 'select', 'scale', 'file', 'photo']);
    $table->json('options')->nullable(); // список опций или параметры шкалы
    $table->json('validation_rules')->nullable(); // min, max, mimes и т.д.
    $table->string('key'); // уникальный идентификатор поля
    $table->string('correct_answer')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
