<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceStandard extends Model
{
    use HasFactory;

    protected $fillable = ['subfunction_id', 'code', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function subfunction()
    {
        return $this->belongsTo(Subfunction::class);
    }

    public function capacities()
    {
        return $this->hasMany(Capacity::class);
    }
}