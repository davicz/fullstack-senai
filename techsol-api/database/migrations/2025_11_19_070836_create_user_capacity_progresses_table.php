<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_capacity_progresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('capacity_id')
                ->constrained('capacities')
                ->onDelete('cascade');

            $table->foreignId('course_id')
                ->constrained('courses')
                ->onDelete('cascade');

            // Estatísticas básicas
            $table->integer('total_responses')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);

            // Pontuação acumulada naquela capacidade
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(0);

            // Progresso em porcentagem (0–100)
            $table->decimal('progress_percent', 5, 2)->default(0);

            // Última resposta nessa capacidade
            $table->timestamp('last_answered_at')->nullable();

            $table->timestamps();

            // Um registro por aluno / capacidade / curso
            $table->unique(['user_id', 'capacity_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_capacity_progresses');
    }
};
