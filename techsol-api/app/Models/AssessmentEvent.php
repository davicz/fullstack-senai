<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AssessmentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'start_date',
        'end_date',
        'exam_start_date',
        'exam_end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'exam_start_date' => 'date',
        'exam_end_date' => 'date',
    ];

    // ---------------------------------
    // Relações
    // ---------------------------------

    public function participants()
    {
        return $this->hasMany(AssessmentEventUser::class);
    }

    // ---------------------------------
    // Scopes
    // ---------------------------------

    public function scopeSaep($query)
    {
        return $query->where('type', 'saep');
    }

    public function scopeDr($query)
    {
        return $query->where('type', 'dr');
    }

    public function scopeDn($query)
    {
        return $query->where('type', 'dn');
    }

    // ---------------------------------
    // Acessores
    // ---------------------------------

    /**
     * Status calculado automaticamente se o campo 'status' estiver nulo:
     * - planned  (ainda não começou)
     * - open     (entre start_date e end_date)
     * - closed   (já passou)
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status) {
            return $this->status;
        }

        $today = Carbon::today();

        if ($this->start_date && $today->lt($this->start_date)) {
            return 'planned';
        }

        if ($this->end_date && $today->gt($this->end_date)) {
            return 'closed';
        }

        if ($this->start_date && $this->end_date &&
            $today->between($this->start_date, $this->end_date)) {
            return 'open';
        }

        // fallback
        return 'planned';
    }
}
