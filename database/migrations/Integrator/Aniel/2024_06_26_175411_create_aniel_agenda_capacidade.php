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
            $table->foreignId('servico_id')->constrained('aniel_agenda_servicos');
            $table->enum('periodo', ['manha', 'tarde', 'noite']);
            $table->integer('capacidade');
            $table->date('data_inicio');
            $table->date('data_fim');
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
