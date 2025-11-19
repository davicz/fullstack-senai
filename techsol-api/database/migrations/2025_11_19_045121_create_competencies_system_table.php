<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistema de Competências SENAI - Estrutura Hierárquica Correta
     * 
     * Hierarquia: Conhecimento → Função → Subfunção → Padrão → Capacidade
     * A questão se vincula à CAPACIDADE (nível mais específico)
     */
    public function up(): void
    {
        // 1. CONHECIMENTOS (Nível mais geral)
        Schema::create('knowledges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: "CON-001"
            $table->string('name'); // Ex: "Conceitos de administração"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. FUNÇÕES (Pertencem a um Conhecimento)
        Schema::create('functions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_id')->constrained('knowledges')->onDelete('cascade');
            $table->string('code')->unique(); // Ex: "F-001"
            $table->string('name'); // Ex: "Executar atividades administrativas"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. SUBFUNÇÕES (Pertencem a uma Função)
        Schema::create('subfunctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('function_id')->constrained('functions')->onDelete('cascade');
            $table->string('code')->unique(); // Ex: "1.4"
            $table->string('name'); // Ex: "Auxiliar na realização dos processos de RH"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. PADRÕES DE DESEMPENHO (Pertencem a uma Subfunção)
        Schema::create('performance_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfunction_id')->constrained('subfunctions')->onDelete('cascade');
            $table->string('code')->unique(); // Ex: "1.4.5"
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. CAPACIDADES (Pertencem a um Padrão, nível mais específico)
        Schema::create('capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_standard_id')->constrained('performance_standards')->onDelete('cascade');
            $table->string('code')->unique(); // Ex: "C26"
            $table->string('name'); // Ex: "Reconhecer as teorias da administração"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. VINCULAR CAPACIDADES A CURSOS
        Schema::create('capacity_course', function (Blueprint $table) {
            $table->foreignId('capacity_id')->constrained('capacities')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->integer('workload')->nullable()->comment('Carga horária em horas');
            $table->integer('semester')->nullable();
            $table->primary(['capacity_id', 'course_id']);
            $table->timestamps();
        });

        // 7. ADICIONAR CAPACIDADE À QUESTÃO (a questão se vincula à capacidade)
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('capacity_id')->nullable()->constrained('capacities')->after('evaluation_id');
        });

        // 8. ESTATÍSTICAS DE QUESTÕES (para cálculo de dificuldade e acertos)
        Schema::create('question_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            // Contagem única de respostas por usuário
            $table->integer('total_responses')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            
            // Percentual de acertos
            $table->decimal('accuracy_rate', 5, 2)->default(0)->comment('Taxa de acertos 0-100');
            
            // Dificuldade calculada automaticamente
            $table->enum('difficulty_level', ['muito_facil', 'facil', 'medio', 'dificil', 'muito_dificil'])->default('medio');
            
            // Distribuição por alternativa (para questões múltipla escolha)
            $table->integer('option_a_count')->default(0);
            $table->integer('option_b_count')->default(0);
            $table->integer('option_c_count')->default(0);
            $table->integer('option_d_count')->default(0);
            $table->integer('option_e_count')->default(0)->nullable();
            
            $table->timestamps();
            
            $table->unique('question_id');
        });

        // 9. RASTREAMENTO DE RESPOSTAS ÚNICAS (para não contar duplicadas)
        Schema::create('user_question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('answer_id')->nullable()->constrained('answers')->onDelete('cascade');
            
            // Primeira resposta dada (para estatísticas)
            $table->boolean('is_correct')->nullable();
            $table->string('selected_option')->nullable(); // A, B, C, D, E
            
            $table->timestamp('first_answered_at')->nullable();
            $table->timestamp('last_answered_at')->nullable();
            $table->integer('attempt_count')->default(1);
            
            $table->timestamps();
            
            // Apenas uma entrada por usuário/questão
            $table->unique(['user_id', 'question_id']);
        });

        // 10. ADICIONAR GABARITO FIXO ÀS OPÇÕES
        Schema::table('options', function (Blueprint $table) {
            $table->enum('letter', ['A', 'B', 'C', 'D', 'E'])->nullable()->after('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropColumn('letter');
        });
        
        Schema::dropIfExists('user_question_attempts');
        Schema::dropIfExists('question_statistics');
        
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['capacity_id']);
            $table->dropColumn('capacity_id');
        });
        
        Schema::dropIfExists('capacity_course');
        Schema::dropIfExists('capacities');
        Schema::dropIfExists('performance_standards');
        Schema::dropIfExists('subfunctions');
        Schema::dropIfExists('functions');
        Schema::dropIfExists('knowledges');
    }
};