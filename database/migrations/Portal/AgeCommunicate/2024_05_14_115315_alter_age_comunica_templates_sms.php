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
        Schema::rename('age_comunica_templates_sms', 'age_comunica_templates');

        Schema::table('age_comunica_templates', function (Blueprint $table) {
            $table->string('template_integradora')->after('titulo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('age_comunica_templates', function (Blueprint $table) {
            $table->dropColumn('template_integradora');
        });
    }
};
