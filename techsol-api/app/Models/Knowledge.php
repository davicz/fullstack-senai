<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    use HasFactory;

    protected $table = 'knowledges';
    protected $fillable = ['code', 'name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function functions()
    {
        return $this->hasMany(FunctionModel::class);
    }
}