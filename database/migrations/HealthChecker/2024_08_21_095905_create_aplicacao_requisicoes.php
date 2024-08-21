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
        Schema::connection('healthChecker')->create('aplicacao_requisicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplicacao_id')->constrained('aplicacoes');
            $table->integer('usuario_id');
            $table->string('uri');
            $table->string('metodo');
            $table->integer('status');
            $table->json('resposta');
            $table->json('corpo_requisicao')->nullable();
            $table->json('cabecalhos_requisicao')->nullable();
            $table->integer('tempo_resposta')->nullable();
            $table->string('ip_cliente')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacao_requisicoes');
    }
};
