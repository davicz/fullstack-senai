<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalDepartment;
use App\Models\OperationalUnit;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- LIMPEZA PARA GARANTIR UM ESTADO CONSISTENTE ---
        // (Opcional se você sempre usa migrate:fresh, mas é uma boa prática)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        RegionalDepartment::truncate();
        OperationalUnit::truncate();
        Course::truncate();
        SchoolClass::truncate();
        Evaluation::truncate();
        DB::table('user_role')->truncate();
        DB::table('class_user')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. CRIAÇÃO DE ESTRUTURA ORGANIZACIONAL ---
        $dr = RegionalDepartment::create(['name' => 'DR Alagoas']);
        $uo = OperationalUnit::create(['name' => 'UO Maceió', 'regional_department_id' => $dr->id]);

        // --- 2. CRIAÇÃO DE ESTRUTURA ACADÊMICA ---
        $course = Course::create(['name' => 'Técnico em Eletrotécnica']);
        $class = SchoolClass::create([
            'name' => 'ELETRO-2025-MANHA',
            'course_id' => $course->id,
            'operational_unit_id' => $uo->id
        ]);
        
        // --- 3. BUSCA DOS PERFIS ---
        $nationalAdminRole = Role::where('slug', 'national_admin')->first();
        $regionalAdminRole = Role::where('slug', 'regional_admin')->first();
        $unitAdminRole = Role::where('slug', 'unit_admin')->first();
        $teacherRole = Role::where('slug', 'teacher')->first();
        $studentRole = Role::where('slug', 'student')->first();

        // --- 4. CRIAÇÃO DOS USUÁRIOS ---
        $nationalAdmin = User::create(['name' => 'Admin Nacional', 'email' => 'dn@senai.com', 'cpf' => '00000000000', 'password' => Hash::make('Password123!')]);
        $regionalAdmin = User::create(['name' => 'Admin Regional AL', 'email' => 'dr.al@senai.com', 'cpf' => '44444444444', 'password' => Hash::make('Password123!'), 'regional_department_id' => $dr->id]);
        $unitAdmin = User::create(['name' => 'Admin UO Maceió', 'email' => 'uo.maceio@senai.com', 'cpf' => '11111111111', 'password' => Hash::make('Password123!'), 'operational_unit_id' => $uo->id, 'regional_department_id' => $dr->id]);
        $teacher = User::create(['name' => 'Docente Teste', 'email' => 'docente@senai.com', 'cpf' => '66666666666', 'password' => Hash::make('Password123!'), 'operational_unit_id' => $uo->id, 'regional_department_id' => $dr->id]);
        $student = User::create(['name' => 'Aluno Teste', 'email' => 'aluno.teste@senai.com', 'cpf' => '55555555555', 'password' => Hash::make('Password123!'), 'operational_unit_id' => $uo->id, 'regional_department_id' => $dr->id]);
        
        // --- 5. ASSOCIAÇÃO DE PERFIS ---
        $nationalAdmin->roles()->attach($nationalAdminRole->id);
        $regionalAdmin->roles()->attach($regionalAdminRole->id);
        $unitAdmin->roles()->attach($unitAdminRole->id);
        $teacher->roles()->attach($teacherRole->id);
        $student->roles()->attach($studentRole->id);

        // --- 6. ASSOCIAÇÃO DE USUÁRIOS A TURMAS (MATRÍCULA) ---
        $teacher->classes()->attach($class->id);
        $student->classes()->attach($class->id);

        // --- 7. CRIAÇÃO DE UMA AVALIAÇÃO DE EXEMPLO ---
        $evaluation = Evaluation::create([
            'title' => 'Prova de Lógica de Programação - Módulo 1',
            'type' => 'formative',
            'school_class_id' => $class->id,
            'created_by_user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $question = $evaluation->questions()->create([
            'statement' => 'Qual é a saída do comando `echo 1 + "1";` em PHP?',
            'type' => 'multiple_choice',
        ]);

        $question->options()->createMany([
            ['text' => '2', 'is_correct' => true],
            ['text' => "'11'", 'is_correct' => false],
            ['text' => 'Um erro de tipo', 'is_correct' => false],
        ]);

        $this->command->info('Ambiente de teste completo (organizações, usuários, curso, turma e avaliação) criado com sucesso!');
    }
}