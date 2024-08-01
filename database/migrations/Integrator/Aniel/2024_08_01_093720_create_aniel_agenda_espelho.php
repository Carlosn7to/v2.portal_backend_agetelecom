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
        Schema::connection('portal')->create('aniel_agenda_espelho', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo');
            $table->string('servico');
            $table->dateTime('data_agendamento');
            $table->string('confirmacao_cliente')->nullable();
            $table->string('localidade');
            $table->string('solicitante')->nullable();
            $table->string('aprovador')->nullable();
            $table->string('status')->nullable();
            $table->string('cor_indicativa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_espelho');
    }
};
