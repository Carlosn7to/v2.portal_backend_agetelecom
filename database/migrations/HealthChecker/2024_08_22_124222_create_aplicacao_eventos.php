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
        Schema::connection('healthChecker')->create('aplicacao_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplicacao_id')->constrained('aplicacoes');
            $table->enum('status', ['online', 'offline']);
            $table->enum('evento', ['cpu', 'ram', 'disco', 'rede', 'aplicacao', 'servidor']);
            $table->enum('tipo', ['alerta', 'erro', 'informacao']);
            $table->text('descricao')->nullable();
            $table->json('dados')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacao_eventos');
    }
};
