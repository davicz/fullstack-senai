<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Define a relação: um Departamento Regional TEM MUITAS Unidades Operacionais.
     */
    public function operationalUnits()
    {
        return $this->hasMany(OperationalUnit::class);
    }
}