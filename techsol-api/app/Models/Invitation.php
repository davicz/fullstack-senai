<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'cpf',
        'status',
        'expires_at',
        'regional_department_id', // <-- PERMISSÃO ADICIONADA
        'operational_unit_id',    // <-- PERMISSÃO ADICIONADA
    ];

    public function roles()
    {
    return $this->belongsToMany(Role::class, 'invitation_role')
                ->withPivot('regional_department_id', 'operational_unit_id');
    }
}