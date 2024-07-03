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
        Schema::create('aniel_agenda_capacidade_feriados', function (Blueprint $table) {
            $table->id();
            $table->date('data_feriado');
            $table->foreignId('servico_id')->constrained('aniel_agenda_servicos');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->integer('capacidade');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->foreignId('criado_por')->constrained('portal_usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_capacidade_feriados');
    }
};
