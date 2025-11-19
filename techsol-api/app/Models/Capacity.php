<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacity extends Model
{
    use HasFactory;

    protected $fillable = ['performance_standard_id', 'code', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function performanceStandard()
    {
        return $this->belongsTo(PerformanceStandard::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'capacity_course')
            ->withPivot('workload', 'semester')
            ->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Retorna toda a hierarquia da capacidade
     */
    public function getFullHierarchy()
    {
        $this->load([
            'performanceStandard.subfunction.function.knowledge'
        ]);

        return [
            'knowledge' => $this->performanceStandard->subfunction->function->knowledge,
            'function' => $this->performanceStandard->subfunction->function,
            'subfunction' => $this->performanceStandard->subfunction,
            'performance_standard' => $this->performanceStandard,
            'capacity' => $this,
        ];
    }
}