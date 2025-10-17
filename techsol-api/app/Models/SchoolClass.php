<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada com o model.
     *
     * @var string
     */
    protected $table = 'classes'; // <-- ESTA É A LINHA QUE CORRIGE O PROBLEMA

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'course_id',
        'operational_unit_id',
    ];

    /**
     * Relação: Uma Turma pertence a um Curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relação: Uma Turma pertence a uma Unidade Operacional.
     */
    public function operationalUnit()
    {
        return $this->belongsTo(OperationalUnit::class);
    }

    public function users()
    {
    return $this->belongsToMany(User::class, 'class_user');
    }
}