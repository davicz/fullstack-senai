<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('difficulty', ['facil', 'medio', 'dificil'])->default('medio')->after('type');
            $table->integer('points')->default(10)->comment('Pontuação da questão')->after('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'points']);
        });
    }
};