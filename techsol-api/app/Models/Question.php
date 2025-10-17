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
    ];

    protected $casts = [
        'options' => 'array',
    ];

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
}