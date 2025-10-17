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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Ex: "Avaliação Diagnóstica - 1º Semestre"
            $table->string('type')->default('formative'); // Tipo da avaliação, ex: 'formative', 'diagnostic'
            
            // Liga a avaliação a uma turma específica
            $table->foreignId('school_class_id')->constrained('classes');

            // Liga a avaliação ao docente que a criou
            $table->foreignId('created_by_user_id')->constrained('users');
            
            $table->timestamp('scheduled_at')->nullable(); // Data e hora para a qual a avaliação está agendada
            $table->enum('status', ['draft', 'scheduled', 'ongoing', 'completed', 'cancelled'])->default('draft');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
