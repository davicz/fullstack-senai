<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'user_id',
        'answer_content',
        'score',
        'is_correct',
        'answered_at',
        'time_spent',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
        'time_spent' => 'integer',
    ];

    /**
     * Relação: Uma resposta pertence a uma questão.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Relação: Uma resposta pertence a um usuário (aluno).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ao salvar, atualiza o progresso nas capacidades (hierarquia SIAC).
     */
    protected static function booted()
    {
        static::saved(function (Answer $answer) {
            // Só atualiza se já tivermos correção
            if ($answer->is_correct !== null && $answer->question) {
                $answer->updateCapacityProgress();
            }
        });
    }

    /**
     * Atualiza o progresso do aluno na capacidade vinculada à questão.
     */
    public function updateCapacityProgress(): void
    {
        // Carrega questão com capacidade e curso da turma
        $question = $this->question()
            ->with(['capacity', 'evaluation.schoolClass'])
            ->first();

        if (!$question || !$question->capacity || !$question->evaluation || !$question->evaluation->schoolClass) {
            return;
        }

        $capacityId = $question->capacity_id;
        $courseId = $question->evaluation->schoolClass->course_id;

        if (!$capacityId || !$courseId) {
            return;
        }

        // Busca ou cria progresso
        $progress = UserCapacityProgress::firstOrCreate(
            [
                'user_id' => $this->user_id,
                'capacity_id' => $capacityId,
                'course_id' => $courseId,
            ]
        );

        $questionPoints = $question->points ?? 10;
        $score = $this->score ?? 0;
        $isCorrect = (bool) $this->is_correct;

        $progress->updateProgress($score, $questionPoints, $isCorrect);
    }
}
