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
        Schema::create('aniel_agenda_quebras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained('aniel_importacao_ordens');
            $table->date('data');
            $table->string('procolo');
            $table->string('servico');
            $table->string('subservico');
            $table->time('hora_agendamento');
            $table->string('periodo');
            $table->string('status');
            $table->string('descricao');
            $table->string('aberta_por');
            $table->string('setor');
            $table->foreignId('solicitante_id')->nullable()->constrained('portal_usuarios');
            $table->foreignId('aprovador_id')->nullable()->constrained('portal_usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_quebras');
    }
};
