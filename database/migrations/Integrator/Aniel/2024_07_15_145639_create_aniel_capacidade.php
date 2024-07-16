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
        Schema::create('aniel_agenda_capacidade', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->string('dia_semana');
            $table->string('servico');
            $table->enum('periodo', ['manha', 'tarde', 'noite']);
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->integer('capacidade');
            $table->integer('utilizado');
            $table->enum('status', ['aberta', 'fechada', 'reaberta']);
            $table->foreignId('atualizado_por')->constrained('portal_usuarios');
            $table->text('motivo_fechamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_capacidade');
    }
};
