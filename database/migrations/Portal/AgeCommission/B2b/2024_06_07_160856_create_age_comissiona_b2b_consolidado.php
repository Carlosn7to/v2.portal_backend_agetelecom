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
        Schema::create('age_comissiona_b2b_consolidado', function (Blueprint $table) {
            $table->id();
            $table->date('pagamento');
            $table->date('referente');
            $table->string('vendedor');
            $table->json('contrato_info');
            $table->json('comissao_info');
            $table->json('regra_aplicada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_comissiona_b2b_consolidado');
    }
};
