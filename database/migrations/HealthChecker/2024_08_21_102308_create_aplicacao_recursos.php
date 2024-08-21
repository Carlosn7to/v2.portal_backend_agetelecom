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
        Schema::connection('healthChecker')->create('aplicacao_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplicacao_id')->constrained('aplicacoes');
            $table->float('cpu_nucleos_totais');
            $table->float('cpu_uso');
            $table->float('cpu_disponivel');
            $table->bigInteger('ram_total');
            $table->bigInteger('ram_uso');
            $table->bigInteger('disco_total');
            $table->bigInteger('disco_uso');
            $table->time('hora_minuto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacao_recursos');
    }
};
