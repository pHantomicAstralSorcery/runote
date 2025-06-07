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
        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_notebook_instance_id')
                  ->constrained('student_notebook_instances')
                  ->cascadeOnDelete();
            $table->string('response_field_uuid'); // UUID поля, а не ID
            $table->text('user_input')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            // Уникальность по экземпляру ученика и UUID поля
            $table->unique(['student_notebook_instance_id', 'response_field_uuid'], 'student_response_unique_field_instance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_responses');
    }
};
