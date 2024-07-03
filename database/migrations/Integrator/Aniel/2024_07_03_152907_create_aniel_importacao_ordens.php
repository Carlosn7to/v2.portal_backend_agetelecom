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
        Schema::create('aniel_importacao_ordens', function (Blueprint $table) {
            $table->id();
            $table->integer('atendimento_id');
            $table->integer('protocolo');
            $table->integer('contrato_id');
            $table->integer('cliente_id');
            $table->string('cliente_documento');
            $table->string('cliente_nome');
            $table->string('email');
            $table->string('celular_1');
            $table->string('celular_2')->nullable();
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('cidade');
            $table->string('bairro');
            $table->string('cep');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('tipo_imovel')->default('INDIFERENTE');
            $table->string('tipo_servico');
            $table->string('node');
            $table->string('area_despacho');
            $table->text('observacao')->nullable();
            $table->string('grupo')->default('DISTRITO FEDERAL');
            $table->dateTime('data_agendamento')->nullable();
            $table->enum('status', ['PENDENTE', 'IMPORTADA', 'ERRO'])->default('PENDENTE');
            $table->json('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_importacao_ordens');
    }
};
