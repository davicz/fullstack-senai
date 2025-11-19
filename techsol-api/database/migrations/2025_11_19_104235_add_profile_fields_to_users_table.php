<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Só adiciona ADDRESS se não existir
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }

            // Só adiciona CEP se não existir
            if (!Schema::hasColumn('users', 'cep')) {
                $table->string('cep', 10)->nullable();
            }

            // Só adiciona CITY se não existir
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }

            // Só adiciona UF se não existir
            if (!Schema::hasColumn('users', 'uf')) {
                $table->string('uf', 2)->nullable();
            }

            // Só adiciona PHONE se não existir
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            
            // Dados Acadêmicos
            if (!Schema::hasColumn('users', 'education_level')) {
                $table->string('education_level')->nullable();
            }
            if (!Schema::hasColumn('users', 'interest_area')) {
                $table->string('interest_area')->nullable();
            }
            if (!Schema::hasColumn('users', 'interest_course')) {
                $table->string('interest_course')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'phone', 'city', 'uf', 'cep', 
                'education_level', 'interest_area', 'interest_course'
            ]);
        });
    }
};
