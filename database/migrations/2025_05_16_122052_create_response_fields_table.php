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
        Schema::create('response_fields', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // Стабильный UUID для поля
            $table->foreignId('notebook_snapshot_id')
                  ->constrained('notebook_snapshots')
                  ->cascadeOnDelete();
            $table->enum('field_type', ['text', 'select', 'file', 'scale']); // Добавил scale, если он был
            $table->string('label')->nullable(); // Название поля
            $table->integer('order')->default(0); // Порядок поля внутри снимка
            $table->json('validation_rules')->nullable();
            $table->json('correct_answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_fields');
    }
};
