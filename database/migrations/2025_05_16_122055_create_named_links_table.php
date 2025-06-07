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
        // named_links (модифицирована)
        Schema::create('named_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notebook_id')->constrained('notebooks')->cascadeOnDelete(); // Ссылка на оригинальную тетрадь
            $table->string('token', 64)->unique(); // Уникальный токен для ссылки
            $table->string('title')->nullable(); // Имя ученика
            $table->timestamp('created_at')->useCurrent(); // Только дата создания ссылки
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
