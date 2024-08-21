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
        Schema::connection('healthChecker')->create('aplicacao_filas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplicacao_id')->constrained('aplicacoes');
            $table->integer('usuario_id')->nullable();
            $table->string('fila')->nullable();
            $table->string('job');
            $table->text('payload')->nullable();
            $table->string('status')->default('pendente');
            $table->integer('tentativas')->default(0);
            $table->integer('tempo_execucao')->nullable();
            $table->timestamp('inicio_processamento')->nullable();
            $table->timestamp('fim_processamento')->nullable();
            $table->text('mensagem_erro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacao_filas');
    }
};
