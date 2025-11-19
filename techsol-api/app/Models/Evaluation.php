<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'school_class_id',
        'created_by_user_id',
        'scheduled_at',
        'starts_at',         // NOVO: quando a avaliação abre
        'ends_at',           // NOVO: quando a avaliação fecha
        'duration',          // NOVO: duração em minutos
        'status',
        'competencies_evaluated', // NOVO
        'instructions',      // NOVO: instruções para o aluno
        'total_points',      // NOVO: pontuação máxima
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'competencies_evaluated' => 'array',
        'duration' => 'integer',
        'total_points' => 'integer',
    ];

    /**
     * Relação: Uma avaliação tem muitas questões.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relação: Uma avaliação pertence a uma turma.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Relação: Uma avaliação tem muitas respostas ATRAVÉS de suas questões.
     */
    public function answers()
    {
        return $this->hasManyThrough(Answer::class, Question::class);
    }

    /**
     * NOVO: Verifica se a avaliação está disponível para responder
     */
    public function isAvailable(): bool
    {
        $now = now();
        
        if ($this->status !== 'ongoing' && $this->status !== 'scheduled') {
            return false;
        }

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * NOVO: Calcula a pontuação total da avaliação
     */
    public function calculateTotalPoints()
    {
        $this->total_points = $this->questions()->sum('points');
        $this->save();
    }

    /**
     * NOVO: Obtém o desempenho de um aluno específico
     */
    public function getStudentPerformance($userId)
    {
        $answers = $this->answers()
            ->where('user_id', $userId)
            ->with('question')
            ->get();

        $totalScore = $answers->sum('score');
        $maxScore = $this->total_points;

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0,
            'answered_questions' => $answers->count(),
            'total_questions' => $this->questions()->count(),
        ];
    }
}