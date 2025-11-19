<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'workload',
        'level',
        'area',
        'is_active',
    ];

    protected $casts = [
        'workload' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relação: Um curso tem múltiplas competências
     */
    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'course_competency')
            ->withPivot('weight', 'semester')
            ->withTimestamps();
    }

    /**
     * Relação: Um curso tem múltiplas turmas
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
