<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // Importamos o Model 'Role' para poder usá-lo

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->delete(); // Limpa a tabela antes de popular

        Role::create(['name' => 'Administrador Senai', 'slug' => 'national_admin']);     // [cite: 29]
        Role::create(['name' => 'Administrador Regional', 'slug' => 'regional_admin']); // [cite: 32]
        Role::create(['name' => 'Administrador Escola', 'slug' => 'school_admin']);     // [cite: 37]
        Role::create(['name' => 'Docente', 'slug' => 'teacher']);                        // [cite: 39]
        Role::create(['name' => 'Aluno', 'slug' => 'student']);                          // [cite: 42]
    }
}