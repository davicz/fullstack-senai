<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompetencyProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competency_id',
        'course_id',
        'score',
        'attempts',
        'proficiency_level',
        'last_evaluated_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'attempts' => 'integer',
        'last_evaluated_at' => 'datetime',
    ];

    /**
     * Relação: Progresso pertence a um usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação: Progresso pertence a uma competência
     */
    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    /**
     * Relação: Progresso pertence a um curso
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Atualiza o progresso do aluno com base em uma nova resposta
     * 
     * @param float $newScore - Pontuação obtida na questão (0-100)
     */
    public function updateProgress(float $newScore)
    {
        // Incrementa o número de tentativas
        $this->attempts++;

        // Calcula a nova média ponderada
        // Dá mais peso às avaliações recentes
        $currentWeight = $this->attempts - 1;
        $newWeight = 1;
        $totalWeight = $currentWeight + $newWeight;

        $this->score = (($this->score * $currentWeight) + ($newScore * $newWeight)) / $totalWeight;

        // Atualiza o nível de proficiência baseado na pontuação
        $this->proficiency_level = $this->calculateProficiencyLevel($this->score);

        // Atualiza a data da última avaliação
        $this->last_evaluated_at = now();

        $this->save();
    }

    /**
     * Calcula o nível de proficiência baseado na pontuação
     */
    private function calculateProficiencyLevel(float $score): string
    {
        if ($score < 40) {
            return 'developing';
        } elseif ($score < 60) {
            return 'proficient';
        } elseif ($score < 80) {
            return 'advanced';
        } else {
            return 'expert';
        }
    }

    /**
     * Scope: Progresso por nível de proficiência
     */
    public function scopeByProficiency($query, string $level)
    {
        return $query->where('proficiency_level', $level);
    }

    /**
     * Scope: Progresso com pontuação mínima
     */
    public function scopeMinScore($query, float $minScore)
    {
        return $query->where('score', '>=', $minScore);
    }
}