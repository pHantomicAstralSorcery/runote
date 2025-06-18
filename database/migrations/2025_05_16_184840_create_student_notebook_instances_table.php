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
        Schema::create('student_notebook_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('named_link_id')->constrained('named_links')->cascadeOnDelete();
            $table->foreignId('notebook_snapshot_id')->constrained('notebook_snapshots')->cascadeOnDelete();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->timestamp('last_accessed_at')->useCurrent(); // Время последнего доступа
            $table->integer('last_active_minutes')->default(0); // Количество минут активности
            // Вы можете добавить другие поля, если необходимо отслеживать прогресс или состояние
            // Например, 'completion_percentage', 'is_completed', 'score' и т.д.

            $table->timestamps();
            $table->unique('named_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notebook_instances');
    }
};
