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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            // Liga a resposta a uma questão específica
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');

            // Liga a resposta a um aluno específico
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Guarda a resposta do aluno. Pode ser um texto para questões descritivas
            // ou a chave da opção escolhida (ex: "a", "b") para múltipla escolha.
            $table->text('answer_content');
            
            // Guarda a nota ou pontuação para esta resposta específica, se aplicável
            $table->decimal('score', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
