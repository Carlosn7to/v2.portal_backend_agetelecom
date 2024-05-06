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
        Schema::create('age_comunica_envios_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('age_comunica_envios');
            $table->string('bulk_id')->nullable();
            $table->string('mensagem_id')->nullable();
            $table->string('enviado_para')->nullable();
            $table->json('resposta_webhook')->nullable();
            $table->enum('status', ['criado', 'atualizado', 'inalterado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_comunica_envios_logs');
    }
};
