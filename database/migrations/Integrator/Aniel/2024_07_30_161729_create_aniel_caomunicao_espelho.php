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
        Schema::connection('portal')->create('aniel_agenda_comunicacao_espelho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained('aniel_importacao_ordens');
            $table->string('protocolo');
            $table->integer('status_aniel');
            $table->string('status_aniel_descricao');
            $table->dateTime('data_agendamento');
            $table->boolean('envio_confirmacao')->default(false);
            $table->boolean('envio_deslocamento')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_comunicacao_espelho');
    }
};
