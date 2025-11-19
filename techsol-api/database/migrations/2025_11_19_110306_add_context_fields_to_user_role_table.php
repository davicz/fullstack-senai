<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_role', function (Blueprint $table) {
            // Adiciona a coluna de Departamento Regional, se não existir
            if (!Schema::hasColumn('user_role', 'regional_department_id')) {
                $table->foreignId('regional_department_id')
                      ->nullable()
                      ->constrained('regional_departments')
                      ->onDelete('cascade');
            }

            // Adiciona a coluna de Unidade Operacional, se não existir
            if (!Schema::hasColumn('user_role', 'operational_unit_id')) {
                $table->foreignId('operational_unit_id')
                      ->nullable()
                      ->constrained('operational_units')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('user_role', function (Blueprint $table) {
            $table->dropForeign(['regional_department_id']);
            $table->dropForeign(['operational_unit_id']);
            $table->dropColumn(['regional_department_id', 'operational_unit_id']);
        });
    }
};