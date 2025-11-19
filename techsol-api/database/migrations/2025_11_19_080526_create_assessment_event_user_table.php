<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_event_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_event_id')
                ->constrained('assessment_events')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Turma do aluno no momento do evento (ajuda nos filtros)
            $table->foreignId('school_class_id')
                ->nullable()
                ->constrained('classes')
                ->nullOnDelete();

            // Agendamento da prova (data em que o aluno vai realizar)
            $table->date('scheduled_date')->nullable();

            // Convite / credencial
            $table->timestamp('invite_sent_at')->nullable();
            $table->string('credential_code')->nullable();
            $table->timestamp('credential_sent_at')->nullable();

            // Quando efetivamente concluiu a prova
            $table->timestamp('completed_at')->nullable();

            // Status agregado para facilitar filtros:
            // not_scheduled / scheduled / invited / completed / cancelled / absent...
            $table->string('status')->default('not_scheduled');

            $table->timestamps();

            $table->unique(['assessment_event_id', 'user_id'], 'assessment_event_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_event_user');
    }
};
