<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de Competências do SENAI
     * 
     * Uma competência é uma habilidade que o aluno deve desenvolver.
     * Ex: "Interpretar desenhos técnicos", "Operar máquinas CNC", etc.
     */
    public function up(): void
    {
        // 1. TABELA DE COMPETÊNCIAS
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: "COMP-001"
            $table->string('name'); // Nome da competência
            $table->text('description')->nullable(); // Descrição detalhada
            
            // Competências podem ser organizadas hierarquicamente
            $table->foreignId('parent_id')->nullable()->constrained('competencies')->onDelete('cascade');
            
            // Nível de complexidade (1-5)
            $table->integer('level')->default(1); // 1=Básico, 5=Avançado
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. MATRIZ DE COMPETÊNCIAS POR CURSO
        // Relaciona quais competências fazem parte de cada curso
        Schema::create('course_competency', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('competency_id')->constrained()->onDelete('cascade');
            
            // Peso/importância desta competência no curso (0-100)
            $table->integer('weight')->default(100);
            
            // Em qual semestre/módulo esta competência é trabalhada
            $table->integer('semester')->nullable();
            
            $table->primary(['course_id', 'competency_id']);
            $table->timestamps();
        });

        // 3. VINCULAR QUESTÕES A COMPETÊNCIAS
        // Uma questão pode avaliar múltiplas competências
        Schema::create('competency_question', function (Blueprint $table) {
            $table->foreignId('competency_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            
            // Peso desta competência na questão (caso avalie múltiplas)
            $table->integer('weight')->default(100);
            
            $table->primary(['competency_id', 'question_id']);
        });

        // 4. DESEMPENHO DO ALUNO POR COMPETÊNCIA
        // Registra o progresso do aluno em cada competência ao longo do tempo
        Schema::create('user_competency_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('competency_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            
            // Pontuação acumulada (0-100)
            $table->decimal('score', 5, 2)->default(0);
            
            // Número de vezes que foi avaliado nesta competência
            $table->integer('attempts')->default(0);
            
            // Status do desenvolvimento
            $table->enum('proficiency_level', [
                'not_started',      // Não iniciado
                'developing',       // Em desenvolvimento
                'proficient',       // Proficiente
                'advanced',         // Avançado
                'expert'           // Expert
            ])->default('not_started');
            
            // Última avaliação
            $table->timestamp('last_evaluated_at')->nullable();
            
            $table->timestamps();
            
            // Um aluno tem apenas um registro de progresso por competência por curso
            $table->unique(['user_id', 'competency_id', 'course_id']);
        });

        // 5. ADICIONAR CAMPO DE COMPETÊNCIAS NA AVALIAÇÃO
        // Para identificar rapidamente quais competências a avaliação cobre
        Schema::table('evaluations', function (Blueprint $table) {
            // JSON com IDs das competências principais desta avaliação
            $table->json('competencies_evaluated')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('competencies_evaluated');
        });
        
        Schema::dropIfExists('user_competency_progress');
        Schema::dropIfExists('competency_question');
        Schema::dropIfExists('course_competency');
        Schema::dropIfExists('competencies');
    }
};