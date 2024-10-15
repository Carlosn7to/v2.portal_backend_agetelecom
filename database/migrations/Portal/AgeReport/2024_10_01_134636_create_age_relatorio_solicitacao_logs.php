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
        Schema::create('age_relatorio_solicitacao_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_id')->constrained('age_relatorio_solicitacoes');
            $table->foreignId('usuario_id')->constrained('portal_usuarios');
            $table->json('parametros');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_relatorio_solicitacao_logs');
    }
};
