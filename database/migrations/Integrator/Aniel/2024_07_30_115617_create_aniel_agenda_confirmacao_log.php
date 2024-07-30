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
        Schema::create('aniel_agenda_comunicacao_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('aniel_agenda_comunicacao');
            $table->enum('status_envio',
                ['pendente', 'enviado', 'erro']
            )->default('pendente');
            $table->enum('status_resposta',
                ['pendente', 'confirmado', 'reagendado', 'atendente']
            )->default('pendente');
            $table->dateTime('atualizado_em');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_comunicacao_log');
    }
};
