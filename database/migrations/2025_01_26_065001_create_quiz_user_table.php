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
Schema::create('quiz_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->double('score', 8, 2)->default(0);
    $table->integer('attempt_number')->default(1); 
$table->unique(['quiz_id', 'user_id', 'attempt_number']);
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->string('status', 20)->default('in_progress');
    $table->timestamps();
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_user');
    }
};
