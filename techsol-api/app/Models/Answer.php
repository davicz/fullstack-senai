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
        'is_correct',        // NOVO
        'answered_at',       // NOVO: timestamp de quando respondeu
        'time_spent',        // NOVO: tempo gasto em segundos
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
     * NOVO: Ao salvar, atualiza o progresso nas competências
     */
    protected static function booted()
    {
        static::saved(function ($answer) {
            if ($answer->is_correct !== null && $answer->question) {
                $answer->updateCompetencyProgress();
            }
        });
    }

    /**
     * NOVO: Atualiza o progresso do aluno nas competências da questão
     */
    public function updateCompetencyProgress()
    {
        $question = $this->question()->with('competencies')->first();
        
        if (!$question || $question->competencies->isEmpty()) {
            return;
        }

        foreach ($question->competencies as $competency) {
            // Busca ou cria o registro de progresso
            $progress = UserCompetencyProgress::firstOrCreate([
                'user_id' => $this->user_id,
                'competency_id' => $competency->id,
                'course_id' => $question->evaluation->schoolClass->course_id,
            ]);

            // Atualiza o progresso com a nova pontuação
            $progress->updateProgress($this->score);
        }
    }
}
