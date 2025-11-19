<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'regional_department_id',
        'code',
        'city',
        'days_submission',
        'approval_percentage'
    ];

    public function regionalDepartment()
    {
        return $this->belongsTo(RegionalDepartment::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
