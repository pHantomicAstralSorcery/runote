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
            $table->string('uuid'); // УБРАЛИ ->unique() здесь!
            $table->foreignId('notebook_snapshot_id')
                  ->constrained('notebook_snapshots')
                  ->cascadeOnDelete();
            $table->enum('field_type', ['text', 'select', 'file', 'scale']);
            $table->string('label')->nullable();
            $table->integer('order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->json('correct_answers')->nullable();
            $table->timestamps();

            // ДОБАВЛЯЕМ КОМПОЗИТНЫЙ УНИКАЛЬНЫЙ ИНДЕКС
            // Это означает, что комбинация notebook_snapshot_id и uuid должна быть уникальной.
            // Один и тот же UUID может быть в разных снимках.
            $table->unique(['notebook_snapshot_id', 'uuid']);
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
