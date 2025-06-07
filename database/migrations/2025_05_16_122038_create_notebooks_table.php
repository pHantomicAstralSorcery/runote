<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('access', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('current_snapshot_id')->nullable(); // Ссылка на текущий опубликованный снимок
            $table->timestamps();

            // Внешний ключ будет добавлен позже, после создания notebook_snapshots
        });

        // notebook_snapshots (НОВАЯ таблица, заменяет notebook_versions)
        Schema::create('notebook_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notebook_id')->constrained('notebooks')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1); // Номер версии для отображения
            $table->longText('content_html'); // Полный HTML-снимок тетради
            $table->timestamps();

            $table->unique(['notebook_id', 'version_number']); // Уникальность по тетради и номеру версии
        });

        // Добавляем внешний ключ к notebooks.current_snapshot_id
        Schema::table('notebooks', function (Blueprint $table) {
            $table->foreign('current_snapshot_id')
                  ->references('id')
                  ->on('notebook_snapshots')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notebook_snapshots');
        Schema::dropIfExists('notebooks');
    }
};