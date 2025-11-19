<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_id',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Relação: Uma competência pode ter uma competência pai (hierarquia)
     */
    public function parent()
    {
        return $this->belongsTo(Competency::class, 'parent_id');
    }

    /**
     * Relação: Uma competência pode ter várias sub-competências
     */
    public function children()
    {
        return $this->hasMany(Competency::class, 'parent_id');
    }

    /**
     * Relação: Competências pertencem a múltiplos cursos
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_competency')
            ->withPivot('weight', 'semester')
            ->withTimestamps();
    }

    /**
     * Relação: Competências são avaliadas por múltiplas questões
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'competency_question')
            ->withPivot('weight');
    }

    /**
     * Relação: Progresso dos alunos nesta competência
     */
    public function userProgress()
    {
        return $this->hasMany(UserCompetencyProgress::class);
    }

    /**
     * Scope: Apenas competências ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Competências raiz (sem pai)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Competências por nível
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}