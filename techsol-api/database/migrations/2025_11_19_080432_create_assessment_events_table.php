<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_events', function (Blueprint $table) {
            $table->id();

            // Ex.: "SAEP 2024-2"
            $table->string('name');

            // Ex.: "SAEP_2024_2" (único)
            $table->string('code')->unique();

            // Tipo do evento: saep / dr / dn
            $table->string('type')->default('saep');

            $table->text('description')->nullable();

            // Período geral do evento (janela de aplicação)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Período em que as provas podem ser realizadas
            $table->date('exam_start_date')->nullable();
            $table->date('exam_end_date')->nullable();

            // Opcional: se quiser forçar um status manual
            // (planned, open, closed). Se null, é calculado pelas datas.
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_events');
    }
};
