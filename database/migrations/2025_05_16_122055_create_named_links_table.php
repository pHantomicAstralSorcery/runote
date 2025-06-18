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
            $table->foreignId('notebook_id')->constrained('notebooks')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('title')->nullable();
            
            // ИСПРАВЛЕНИЕ: Добавлен столбец is_active
            $table->boolean('is_active')->default(true); 
            
            // ИСПРАВЛЕНИЕ: Используем стандартный метод timestamps()
            // Он создает created_at и updated_at, которые ожидает Eloquent
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
