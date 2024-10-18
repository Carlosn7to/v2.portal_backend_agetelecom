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
        Schema::create('age_modulo_privilegios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('age_modulos');
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->json('permissoes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_modulo_privilegios');
    }
};
