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
            $table->string('title');
            $table->enum('access', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('current_version_id')->nullable();

            // **Новое поле для хранения снимка**
            $table->json('oldSnapshot')->nullable();

            $table->foreign('current_version_id')
                  ->references('id')
                  ->on('notebook_versions')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('notebooks', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });

        Schema::dropIfExists('notebooks');
    }
};
