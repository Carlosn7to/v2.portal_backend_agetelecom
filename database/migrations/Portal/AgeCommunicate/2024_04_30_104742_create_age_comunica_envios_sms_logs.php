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
        Schema::create('age_comunica_envios_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('bulk_id');
            $table->string('mensagem_id');
            $table->string('celular');
            $table->json('resposta_infobip');
            $table->enum('status', ['criado', 'atualizado', 'inalterado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_comunica_envios_sms_logs');
    }
};
