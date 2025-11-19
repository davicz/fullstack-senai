<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCapacityProgress extends Model
{
    use HasFactory;

    protected $table = 'user_capacity_progresses';

    protected $fillable = [
        'user_id',
        'capacity_id',
        'course_id',
        'total_responses',
        'correct_answers',
        'wrong_answers',
        'total_score',
        'max_score',
        'progress_percent',
        'last_answered_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'progress_percent' => 'decimal:2',
        'last_answered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function capacity()
    {
        return $this->belongsTo(Capacity::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Atualiza o progresso do aluno nessa capacidade
     */
    public function updateProgress(float $score, float $questionPoints, bool $isCorrect): void
    {
        $this->total_responses++;

        if ($isCorrect) {
            $this->correct_answers++;
        } else {
            $this->wrong_answers++;
        }

        $this->total_score += $score;
        $this->max_score += $questionPoints;

        $this->progress_percent = $this->max_score > 0
            ? ($this->total_score / $this->max_score) * 100
            : 0;

        $this->last_answered_at = now();

        $this->save();
    }
}
