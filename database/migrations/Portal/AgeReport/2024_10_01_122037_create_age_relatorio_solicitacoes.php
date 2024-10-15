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
        Schema::create('age_relatorio_solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('age_relatorios');
            $table->foreignId('usuario_id')->constrained('portal_usuarios');
            $table->enum('tipo', ['unico', 'periodico', 'integracao']);
            $table->enum('status', ['pendente', 'processando', 'concluido', 'erro', 'enviado']);
            $table->string('caminho_arquivo');
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_relatorio_solicitacoes');
    }
};
