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
        Schema::create('age_comunica_envios_sms', function (Blueprint $table) {
            $table->id();
            $table->string('bulk_id');
            $table->string('mensagem_id');
            $table->integer('contrato_id');
            $table->integer('fatura_id');
            $table->string('celular');
            $table->string('celular_voalle');
            $table->string('segregacao');
            $table->smallInteger('regra');
            $table->enum('status', ['enviado', 'entregue', 'erro']);
            $table->json('erro')->nullable();
            $table->foreignId('template_id')->constrained('age_comunica_templates_sms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_comunica_envios_sms');
    }
};
