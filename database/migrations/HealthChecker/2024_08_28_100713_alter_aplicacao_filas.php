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
        Schema::connection('healthChecker')->table('aplicacao_filas', function (Blueprint $table) {
            $table->integer('processadas')->default(0);
            $table->integer('erros')->default(0);
            $table->integer('pendentes')->default(0);
            $table->dropColumn('usuario_id');
            $table->dropColumn('fila');
            $table->dropColumn('job');
            $table->dropColumn('payload');
            $table->dropColumn('status');
            $table->dropColumn('tentativas');
            $table->dropColumn('tempo_execucao');
            $table->dropColumn('inicio_processamento');
            $table->dropColumn('fim_processamento');
            $table->dropColumn('mensagem_erro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
