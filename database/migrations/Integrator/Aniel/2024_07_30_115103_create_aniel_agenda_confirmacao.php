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
        Schema::create('aniel_agenda_comunicacao', function (Blueprint $table) {
            $table->id();
            $table->string('mensagem_id');
            $table->foreignId('os_id')->constrained('aniel_importacao_ordens');
            $table->string('protocolo');
            $table->string('celular_cliente');
            $table->string('template');
            $table->json('dados');
            $table->enum('status_envio',
                ['pendente', 'enviado', 'erro']
            )->default('pendente');
            $table->enum('status_resposta',
                ['pendente', 'confirmado', 'reagendado', 'atendente']
            )->default('pendente');
            $table->dateTime('data_envio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_comunicacao');
    }
};
