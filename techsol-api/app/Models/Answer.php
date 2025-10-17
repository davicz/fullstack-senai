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
}