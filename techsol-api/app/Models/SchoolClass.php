<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'codigo',
        'origem',
        'turno',
        'course_id',
        'operational_unit_id',
        'regional_department_id',
        'docente_responsavel',
        'quantidade_alunos'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function operationalUnit() {
        return $this->belongsTo(OperationalUnit::class);
    }

    public function regionalDepartment() {
        return $this->belongsTo(RegionalDepartment::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_user')
                    ->withPivot('role')
                    ->wherePivot('role', 'teacher');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_user')
                    ->withPivot('role')
                    ->wherePivot('role', 'student');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'class_user')->withPivot('role');
    }
}