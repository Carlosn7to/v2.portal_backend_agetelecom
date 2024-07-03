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
        Schema::create('aniel_agenda_capacidade_semanal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('aniel_agenda_servicos');
            $table->enum('dia_semana',
                ['domingo', 'segunda-feira', 'terca-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sabado']);
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->integer('capacidade');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->foreignId('criado_por')->constrained('portal_usuarios');
            $table->foreignId('finalizado_por')->constrained('portal_usuarios');
            $table->date('data_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_capacidade_semanal');
    }
};
