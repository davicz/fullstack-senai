<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'statement',
        'type',
        'difficulty', // NOVO: fácil, médio, difícil
        'points',     // NOVO: pontuação da questão
        'capacity_id',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * NOVO: Relação com competências
     */
    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'competency_question')
            ->withPivot('weight');
    }

    /**
     * Relação: Uma questão tem muitas opções.
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Relação: Uma questão pertence a uma avaliação.
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    /**
     * Relação: Uma questão tem muitas respostas (uma por aluno).
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * NOVO: Verifica se a resposta do aluno está correta
     */
    public function checkAnswer($answerContent): bool
    {
        if ($this->type === 'multiple_choice') {
            $correctOption = $this->options()->where('is_correct', true)->first();
            return $correctOption && $correctOption->id == $answerContent;
        }
        
        // Para questões descritivas, a correção é manual
        return false;
    }

    /**
     * NOVO: Calcula pontuação baseada na resposta
     */
    public function calculateScore($answerContent): float
    {
        if ($this->checkAnswer($answerContent)) {
            return $this->points ?? 10; // Pontuação padrão se não definida
        }
        return 0;
    }

        // Adicionar relações
    public function capacity()
    {
        return $this->belongsTo(Capacity::class);
    }

    public function statistics()
    {
        return $this->hasOne(QuestionStatistic::class);
    }

    public function attempts()
    {
        return $this->hasMany(UserQuestionAttempt::class);
    }

    public function getIdentifierAttribute()
    {
        return 'SIAC_' . $this->id;
    }

    public function getCorrectAnswerLetter()
    {
        $correctOption = $this->options()->where('is_correct', true)->first();
        return $correctOption ? $correctOption->letter : null;
    }
}