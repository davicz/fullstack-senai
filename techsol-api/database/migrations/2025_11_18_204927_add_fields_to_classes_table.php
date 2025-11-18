<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {

            // Novos campos essenciais
            $table->string('codigo')->nullable()->after('id');
            $table->string('origem')->default('SIAC')->after('codigo');
            $table->enum('turno', ['manha','tarde','noite','integral'])->nullable()->after('origem');

            $table->string('docente_responsavel')->nullable()->after('operational_unit_id');
            $table->integer('quantidade_alunos')->nullable()->after('docente_responsavel');

            // DR da turma
            $table->foreignId('regional_department_id')
                  ->nullable()
                  ->constrained('regional_departments')
                  ->after('operational_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn([
                'codigo',
                'origem',
                'turno',
                'docente_responsavel',
                'quantidade_alunos',
                'regional_department_id'
            ]);
        });
    }
};
