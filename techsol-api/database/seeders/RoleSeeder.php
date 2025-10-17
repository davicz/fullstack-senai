<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa a tabela para garantir consistência
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Role::create(['name' => 'Administrador Senai', 'slug' => 'national_admin']);
        Role::create(['name' => 'Administrador Regional', 'slug' => 'regional_admin']);
        Role::create(['name' => 'Administrador Escola', 'slug' => 'unit_admin']); // <- O NOME CORRETO
        Role::create(['name' => 'Docente', 'slug' => 'teacher']);
        Role::create(['name' => 'Aluno', 'slug' => 'student']);
    }
}