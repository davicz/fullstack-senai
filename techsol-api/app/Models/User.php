<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'role_id', // Se ainda usar esse campo legado
        'regional_department_id',
        'operational_unit_id',
        // Novos campos
        'address', 'cep', 'city', 'uf', 'phone',
        'education_level', 'interest_area', 'interest_course'
    ];

    protected $with = ['roles'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relação: Um usuário pertence a um Perfil (Role).
     */
    // app/Models/User.php
    public function roles()
    {
        // Removemos o withPivot, pois não há mais colunas extras
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Relação: Um usuário pode pertencer a um Departamento Regional.
     */
    public function regionalDepartment()
    {
        return $this->belongsTo(RegionalDepartment::class);
    }

    /**
     * Relação: Um usuário pode pertencer a uma Unidade Operacional.
     */
    public function operationalUnit()
    {
        return $this->belongsTo(OperationalUnit::class);
    }
    
    public function classes()
    {
    return $this->belongsToMany(SchoolClass::class, 'class_user');
    }
}