<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subfunction extends Model
{
    use HasFactory;

    protected $fillable = ['function_id', 'code', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function function()
    {
        return $this->belongsTo(FunctionModel::class, 'function_id');
    }

    public function performanceStandards()
    {
        return $this->hasMany(PerformanceStandard::class);
    }
}