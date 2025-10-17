<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
    ];

    /**
     * Relação: Uma opção pertence a uma questão.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}