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
            
            // Verifica um por um. Se não existir, cria.
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }

            if (!Schema::hasColumn('users', 'cep')) {
                $table->string('cep', 10)->nullable();
            }

            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }

            if (!Schema::hasColumn('users', 'uf')) {
                $table->string('uf', 2)->nullable();
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            
            // Campos acadêmicos (provavelmente esses não existem ainda, mas garantimos)
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
                'address', 'cep', 'city', 'uf', 'phone', 
                'education_level', 'interest_area', 'interest_course'
            ]);
        });
    }
};
