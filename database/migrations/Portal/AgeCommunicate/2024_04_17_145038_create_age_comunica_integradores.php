<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('age_comunica_integradores', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->json('configuracao');
            $table->string('canais', 100);
            $table->json('variaveis_disponiveis');
            $table->text('instrucao');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->foreignId('criado_por')->constrained('portal_usuarios');
            $table->foreignId('atualizado_por')->constrained('portal_usuarios');
            $table->foreignId('deletado_por')->nullable()->constrained('portal_usuarios');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_comunica_integradores');
    }
};
