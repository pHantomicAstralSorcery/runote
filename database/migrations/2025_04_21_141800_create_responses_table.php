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
Schema::create('responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('named_link_id')->constrained()->onDelete('cascade');
    $table->foreignId('field_id')->constrained()->onDelete('cascade');
    $table->text('value');
    $table->boolean('is_correct')->nullable(); // авто или ручная проверка
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
