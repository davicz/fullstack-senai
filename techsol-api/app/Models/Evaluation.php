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
        'status',
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
}