<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionModel extends Model
{
    use HasFactory;

    protected $table = 'functions';
    protected $fillable = ['knowledge_id', 'code', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function knowledge()
    {
        return $this->belongsTo(Knowledge::class);
    }

    public function subfunctions()
    {
        return $this->hasMany(Subfunction::class, 'function_id');
    }
}