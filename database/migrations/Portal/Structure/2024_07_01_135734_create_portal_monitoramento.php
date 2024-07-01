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
        Schema::connection('portal')->create('portal_monitoramento', function (Blueprint $table) {
            $table->id();
            $table->string('servico');
            $table->string('comando');
            $table->string('descricao');
            $table->json('log');
            $table->dateTime('data_hora_alerta');
            $table->dateTime('data_hora_resolucao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_monitoramento');
    }
};
