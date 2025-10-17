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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            
            // Liga a questão a uma avaliação
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            
            $table->text('statement'); // O enunciado da questão
            $table->string('type')->default('multiple_choice'); // Tipo de questão, ex: 'multiple_choice', 'descriptive'
            
            // Para questões de múltipla escolha, podemos guardar as opções num JSON
            $table->json('options')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
