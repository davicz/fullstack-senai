<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'total_responses',
        'correct_answers',
        'wrong_answers',
        'accuracy_rate',
        'difficulty_level',
        'option_a_count',
        'option_b_count',
        'option_c_count',
        'option_d_count',
        'option_e_count',
    ];

    protected $casts = [
        'accuracy_rate' => 'decimal:2',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Atualiza as estatísticas após uma nova resposta
     */
    public function updateStats($isCorrect, $selectedOption)
    {
        $this->total_responses++;
        
        if ($isCorrect) {
            $this->correct_answers++;
        } else {
            $this->wrong_answers++;
        }

        // Calcula taxa de acertos
        $this->accuracy_rate = $this->total_responses > 0 
            ? ($this->correct_answers / $this->total_responses) * 100 
            : 0;

        // Calcula dificuldade automaticamente
        $this->difficulty_level = $this->calculateDifficulty($this->accuracy_rate);

        // Incrementa contagem da opção selecionada
        $optionField = 'option_' . strtolower($selectedOption) . '_count';
        if (in_array($optionField, ['option_a_count', 'option_b_count', 'option_c_count', 'option_d_count', 'option_e_count'])) {
            $this->$optionField++;
        }

        $this->save();
    }

    /**
     * Calcula dificuldade baseada na taxa de acertos
     */
    private function calculateDifficulty($accuracyRate)
    {
        if ($accuracyRate >= 80) return 'muito_facil';
        if ($accuracyRate >= 50) return 'facil';
        if ($accuracyRate >= 30) return 'medio';
        if ($accuracyRate >= 10) return 'dificil';
        return 'muito_dificil';
    }

    /**
     * Retorna percentual por opção
     */
    public function getOptionPercentages()
    {
        if ($this->total_responses == 0) {
            return [
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0
            ];
        }

        return [
            'A' => round(($this->option_a_count / $this->total_responses) * 100, 2),
            'B' => round(($this->option_b_count / $this->total_responses) * 100, 2),
            'C' => round(($this->option_c_count / $this->total_responses) * 100, 2),
            'D' => round(($this->option_d_count / $this->total_responses) * 100, 2),
            'E' => round(($this->option_e_count / $this->total_responses) * 100, 2),
        ];
    }
}