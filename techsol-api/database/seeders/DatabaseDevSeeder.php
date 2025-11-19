<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

use App\Models\Role;
use App\Models\User;
use App\Models\RegionalDepartment;
use App\Models\OperationalUnit;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Evaluation;
use App\Models\Question;
use App\Models\Option;
use App\Models\Answer;
use App\Models\UserQuestionAttempt;
use App\Models\QuestionStatistic;

use App\Models\Knowledge;
use App\Models\FunctionModel;
use App\Models\Subfunction;
use App\Models\PerformanceStandard;
use App\Models\Capacity;

class DatabaseDevSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // =====================================================
        // 0. LIMPEZA GERAL (DEV APENAS!)
        // =====================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        UserQuestionAttempt::truncate();
        QuestionStatistic::truncate();
        Answer::truncate();
        Option::truncate();
        Question::truncate();
        Evaluation::truncate();

        DB::table('class_user')->truncate();
        DB::table('user_role')->truncate();

        SchoolClass::truncate();
        Course::truncate();
        OperationalUnit::truncate();
        RegionalDepartment::truncate();

        // Hierarquia de competências SENAI
        Capacity::truncate();
        PerformanceStandard::truncate();
        Subfunction::truncate();
        FunctionModel::truncate();
        Knowledge::truncate();

        Role::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =====================================================
        // 1. ROLES (equivalente ao RoleSeeder)
        // =====================================================
        $roleNational   = Role::create(['name' => 'Administrador Senai',   'slug' => 'national_admin']);
        $roleRegional   = Role::create(['name' => 'Administrador Regional','slug' => 'regional_admin']);
        $roleUnit       = Role::create(['name' => 'Administrador Escola',  'slug' => 'unit_admin']);
        $roleTeacher    = Role::create(['name' => 'Docente',               'slug' => 'teacher']);
        $roleStudent    = Role::create(['name' => 'Aluno',                 'slug' => 'student']);

        // =====================================================
        // 2. SISTEMA DE COMPETÊNCIAS SENAI (teu CompetenciesSeeder)
        // =====================================================

        // CONHECIMENTO 1: Conceitos de Administração
        $conhec1 = Knowledge::create([
            'code' => 'CON-001',
            'name' => 'Conceitos de administração',
            'description' => 'Fundamentos e teorias da ciência administrativa',
            'is_active' => true,
        ]);

        $func1 = FunctionModel::create([
            'knowledge_id' => $conhec1->id,
            'code' => 'F-001',
            'name' => 'Executar atividades administrativas nos setores de produção e serviços',
            'description' => 'Realizar tarefas administrativas utilizando técnicas e ferramentas apropriadas',
            'is_active' => true,
        ]);

        $sub1_1 = Subfunction::create([
            'function_id' => $func1->id,
            'code' => '1.1',
            'name' => 'Realizar atividades de organização e controle',
            'description' => 'Organizar documentos, arquivos e realizar controles administrativos',
            'is_active' => true,
        ]);

        $pad1_1_1 = PerformanceStandard::create([
            'subfunction_id' => $sub1_1->id,
            'code' => '1.1.1',
            'name' => 'Organizar documentos e arquivos',
            'description' => 'Classificar e arquivar documentos seguindo normas e procedimentos',
            'is_active' => true,
        ]);

        $capC1 = Capacity::create([
            'performance_standard_id' => $pad1_1_1->id,
            'code' => 'C1',
            'name' => 'Identificar tipos de documentos administrativos',
            'description' => 'Reconhecer e classificar diferentes tipos de documentos utilizados na administração',
            'is_active' => true,
        ]);

        $capC2 = Capacity::create([
            'performance_standard_id' => $pad1_1_1->id,
            'code' => 'C2',
            'name' => 'Aplicar técnicas de arquivamento',
            'description' => 'Utilizar métodos de organização e arquivamento de documentos',
            'is_active' => true,
        ]);

        $sub1_4 = Subfunction::create([
            'function_id' => $func1->id,
            'code' => '1.4',
            'name' => 'Auxiliar na realização dos processos de recursos humanos (RH)',
            'description' => 'Apoiar as atividades de gestão de pessoas na organização',
            'is_active' => true,
        ]);

        $pad1_4_5 = PerformanceStandard::create([
            'subfunction_id' => $sub1_4->id,
            'code' => '1.4.5',
            'name' => 'Reconhecer teorias administrativas aplicadas ao RH',
            'description' => 'Identificar teorias da administração nos processos de gestão de pessoas',
            'is_active' => true,
        ]);

        $capC26 = Capacity::create([
            'performance_standard_id' => $pad1_4_5->id,
            'code' => 'C26',
            'name' => 'Reconhecer as teorias da administração nos processos administrativos',
            'description' => 'Identificar e aplicar conceitos das principais teorias administrativas',
            'is_active' => true,
        ]);

        // CONHECIMENTO 2: Processos Mecânicos
        $conhec2 = Knowledge::create([
            'code' => 'CON-002',
            'name' => 'Processos de fabricação mecânica',
            'description' => 'Conhecimentos sobre usinagem e fabricação de peças',
            'is_active' => true,
        ]);

        $func2 = FunctionModel::create([
            'knowledge_id' => $conhec2->id,
            'code' => 'F-002',
            'name' => 'Executar operações de usinagem',
            'description' => 'Realizar processos de usinagem em máquinas convencionais e CNC',
            'is_active' => true,
        ]);

        $sub1_2 = Subfunction::create([
            'function_id' => $func2->id,
            'code' => '1.2',
            'name' => 'Operar máquinas-ferramenta',
            'description' => 'Utilizar tornos, fresas e outras máquinas de usinagem',
            'is_active' => true,
        ]);

        $pad1_2_1 = PerformanceStandard::create([
            'subfunction_id' => $sub1_2->id,
            'code' => '1.2.1',
            'name' => 'Preparar máquina para operação',
            'description' => 'Realizar setup e ajustes necessários na máquina',
            'is_active' => true,
        ]);

        $capC5 = Capacity::create([
            'performance_standard_id' => $pad1_2_1->id,
            'code' => 'C5',
            'name' => 'Identificar ferramentas de corte',
            'description' => 'Reconhecer e selecionar ferramentas adequadas para cada operação',
            'is_active' => true,
        ]);

        $sub1_3 = Subfunction::create([
            'function_id' => $func2->id,
            'code' => '1.3',
            'name' => 'Interpretar desenhos técnicos',
            'description' => 'Ler e compreender projetos mecânicos',
            'is_active' => true,
        ]);

        $pad1_3_3 = PerformanceStandard::create([
            'subfunction_id' => $sub1_3->id,
            'code' => '1.3.3',
            'name' => 'Reconhecer vistas e projeções',
            'description' => 'Identificar vistas ortogonais e sistemas de projeção',
            'is_active' => true,
        ]);

        $capC24 = Capacity::create([
            'performance_standard_id' => $pad1_3_3->id,
            'code' => 'C24',
            'name' => 'Interpretar projeções ortogonais',
            'description' => 'Compreender representação de peças em vistas ortogonais',
            'is_active' => true,
        ]);

        // CONHECIMENTO 3: Eletrônica
        $conhec3 = Knowledge::create([
            'code' => 'CON-003',
            'name' => 'Fundamentos de eletrônica',
            'description' => 'Princípios básicos de circuitos eletrônicos',
            'is_active' => true,
        ]);

        $func3 = FunctionModel::create([
            'knowledge_id' => $conhec3->id,
            'code' => 'F-003',
            'name' => 'Montar e testar circuitos eletrônicos',
            'description' => 'Realizar montagem e testes de circuitos eletrônicos básicos',
            'is_active' => true,
        ]);

        $sub2_1 = Subfunction::create([
            'function_id' => $func3->id,
            'code' => '2.1',
            'name' => 'Identificar componentes eletrônicos',
            'description' => 'Reconhecer e especificar componentes eletrônicos',
            'is_active' => true,
        ]);

        $pad2_1_6 = PerformanceStandard::create([
            'subfunction_id' => $sub2_1->id,
            'code' => '2.1.6',
            'name' => 'Reconhecer funções de componentes passivos',
            'description' => 'Identificar resistores, capacitores e indutores',
            'is_active' => true,
        ]);

        $capC39 = Capacity::create([
            'performance_standard_id' => $pad2_1_6->id,
            'code' => 'C39',
            'name' => 'Interpretar código de cores de resistores',
            'description' => 'Determinar valor de resistência através do código de cores',
            'is_active' => true,
        ]);

        // CONHECIMENTO 4: Programação
        $conhec4 = Knowledge::create([
            'code' => 'CON-004',
            'name' => 'Lógica de programação',
            'description' => 'Fundamentos de algoritmos e programação',
            'is_active' => true,
        ]);

        $func4 = FunctionModel::create([
            'knowledge_id' => $conhec4->id,
            'code' => 'F-004',
            'name' => 'Desenvolver algoritmos e programas',
            'description' => 'Criar soluções computacionais através de programação',
            'is_active' => true,
        ]);

        $sub3_4 = Subfunction::create([
            'function_id' => $func4->id,
            'code' => '3.4',
            'name' => 'Aplicar estruturas de controle',
            'description' => 'Utilizar estruturas condicionais e de repetição',
            'is_active' => true,
        ]);

        $pad3_4_1 = PerformanceStandard::create([
            'subfunction_id' => $sub3_4->id,
            'code' => '3.4.1',
            'name' => 'Implementar estruturas condicionais',
            'description' => 'Usar if, else e switch em algoritmos',
            'is_active' => true,
        ]);

        $capC12 = Capacity::create([
            'performance_standard_id' => $pad3_4_1->id,
            'code' => 'C12',
            'name' => 'Reconhecer situações que requerem decisão',
            'description' => 'Identificar quando usar estruturas condicionais',
            'is_active' => true,
        ]);

        $capacityByCode = [
            'C1'  => $capC1,
            'C2'  => $capC2,
            'C26' => $capC26,
            'C5'  => $capC5,
            'C24' => $capC24,
            'C39' => $capC39,
            'C12' => $capC12,
        ];

        // =====================================================
        // 3. REGIONAIS e UOs
        // =====================================================
        $regions = [];
        $uos = [];

        $regionNames = [
            'DR Alagoas',
            'DR Pernambuco',
            'DR Sergipe',
        ];

        foreach ($regionNames as $rName) {
            $rd = RegionalDepartment::create(['name' => $rName]);
            $regions[] = $rd;

            // 2 UOs por DR
            for ($i = 1; $i <= 2; $i++) {
                $uo = OperationalUnit::create([
                    'name' => $rName . " - UO {$i}",
                    'regional_department_id' => $rd->id,
                    'city' => $faker->city,
                    'code' => strtoupper(substr($rName, 3, 2)) . "-UO{$i}",
                ]);
                $uos[] = $uo;
            }
        }

        // =====================================================
        // 4. CURSOS + vínculo com CAPACITIES
        // =====================================================
        $courses = [];

        $coursesData = [
            [
                'name' => 'Técnico em Administração',
                'code' => 'ADM-TEC',
                'area' => 'Gestão',
                'level' => 'Técnico',
                'workload' => 1000,
                'capacity_codes' => ['C1', 'C2', 'C26'],
            ],
            [
                'name' => 'Técnico em Mecânica',
                'code' => 'MEC-TEC',
                'area' => 'Industrial',
                'level' => 'Técnico',
                'workload' => 1200,
                'capacity_codes' => ['C5', 'C24'],
            ],
            [
                'name' => 'Técnico em Eletroeletrônica',
                'code' => 'ELETRO-TEC',
                'area' => 'Industrial',
                'level' => 'Técnico',
                'workload' => 1200,
                'capacity_codes' => ['C39'],
            ],
            [
                'name' => 'Técnico em Informática',
                'code' => 'INFO-TEC',
                'area' => 'Tecnologia',
                'level' => 'Técnico',
                'workload' => 1000,
                'capacity_codes' => ['C12'],
            ],
        ];

        foreach ($coursesData as $cData) {
            /** @var Course $course */
            $course = Course::create([
                'name'        => $cData['name'],
                'code'        => $cData['code'],
                'area'        => $cData['area'],
                'level'       => $cData['level'],
                'workload'    => $cData['workload'],
                'is_active'   => true,
                'description' => null,
            ]);

            $courses[] = $course;

            // vincular capacidades a cursos (pivot capacity_course)
            foreach ($cData['capacity_codes'] as $capCode) {
                if (isset($capacityByCode[$capCode])) {
                    $capacityByCode[$capCode]->courses()->attach($course->id, [
                        'workload' => intval($cData['workload'] / max(1, count($cData['capacity_codes']))),
                        'semester' => 1,
                    ]);
                }
            }
        }

        // =====================================================
        // 5. TURMAS (SchoolClass) por Curso x UO
        // =====================================================
        $classes = [];
        foreach ($courses as $course) {
            foreach ($uos as $uo) {
                $class = SchoolClass::create([
                    'name' => "{$course->code}-{$uo->id}-2025-MANHÃ",
                    'course_id' => $course->id,
                    'operational_unit_id' => $uo->id,
                ]);
                $classes[] = $class;
            }
        }

        // =====================================================
        // 6. USUÁRIOS (admins, docentes, alunos)
        // =====================================================

        // National admin (1)
        $nationalAdmin = User::create([
            'name'  => 'Admin Nacional',
            'email' => 'dn@senai.com',
            'cpf'   => '00000000000',
            'password' => Hash::make('Password123!'),
        ]);
        $nationalAdmin->roles()->attach($roleNational->id);

        // Regionais admins (1 por DR)
        $regionalAdmins = [];
        foreach ($regions as $rd) {
            $u = User::create([
                'name'  => "Admin Regional {$rd->name}",
                'email' => 'dr.' . strtolower(str_replace(' ', '.', $rd->name)) . '@senai.com',
                'cpf'   => $faker->numerify('4##########'),
                'password' => Hash::make('Password123!'),
                'regional_department_id' => $rd->id,
            ]);
            $u->roles()->attach($roleRegional->id);
            $regionalAdmins[] = $u;
        }

        // Unit admins + Teachers por UO
        $unitAdmins = [];
        $teachersByUO = []; // [uo_id => Collection<Teacher>]

        foreach ($uos as $uo) {
            // Unit admin
            $ua = User::create([
                'name'  => "Admin {$uo->name}",
                'email' => 'uo.' . strtolower(str_replace(' ', '.', $uo->name)) . '@senai.com',
                'cpf'   => $faker->numerify('1##########'),
                'password' => Hash::make('Password123!'),
                'operational_unit_id' => $uo->id,
                'regional_department_id' => $uo->regional_department_id,
            ]);
            $ua->roles()->attach($roleUnit->id);
            $unitAdmins[] = $ua;

            // 2 docentes por UO
            $teachersByUO[$uo->id] = collect();
            for ($i = 0; $i < 2; $i++) {
                $t = User::create([
                    'name'  => $faker->name(),
                    'email' => "docente{$uo->id}_{$i}@senai.com",
                    'cpf'   => $faker->numerify('6##########'),
                    'password' => Hash::make('Password123!'),
                    'operational_unit_id' => $uo->id,
                    'regional_department_id' => $uo->regional_department_id,
                ]);
                $t->roles()->attach($roleTeacher->id);
                $teachersByUO[$uo->id]->push($t);
            }
        }

        // 50 alunos brasileiros distribuídos nas turmas
        $students = [];
        $classStudents = []; // [class_id => [student_ids...]]

        foreach ($classes as $class) {
            $classStudents[$class->id] = [];
        }

        for ($i = 0; $i < 50; $i++) {
            /** @var SchoolClass $randomClass */
            $randomClass = $classes[array_rand($classes)];
            $uo = $uos[array_search($randomClass->operational_unit_id, array_column($uos, 'id'))] ?? null;

            // só pra garantir
            $uoModel = collect($uos)->firstWhere('id', $randomClass->operational_unit_id);

            $student = User::create([
                'name'  => $faker->name(),
                'email' => "aluno{$i}@teste.com",
                'cpf'   => $faker->numerify('5##########'),
                'password' => Hash::make('Password123!'),
                'operational_unit_id' => $uoModel?->id,
                'regional_department_id' => $uoModel?->regional_department_id,
            ]);
            $student->roles()->attach($roleStudent->id);
            $student->classes()->attach($randomClass->id);

            $students[] = $student;
            $classStudents[$randomClass->id][] = $student->id;
        }

        // Vincular docentes às turmas (mesma UO)
        foreach ($classes as $class) {
            $uoId = $class->operational_unit_id;
            $teachers = $teachersByUO[$uoId] ?? collect();
            if ($teachers->isNotEmpty()) {
                $teacher = $teachers->random();
                $teacher->classes()->attach($class->id);
            }
        }

        // =====================================================
        // 7. AVALIAÇÕES POR TURMA
        // =====================================================
        $evaluations = [];
        foreach ($classes as $class) {
            $teachers = $teachersByUO[$class->operational_unit_id] ?? collect();
            if ($teachers->isEmpty()) {
                continue;
            }
            $teacher = $teachers->random();

            $evaluation = Evaluation::create([
                'title' => "Avaliação - {$class->name}",
                'type' => 'formative',
                'school_class_id' => $class->id,
                'created_by_user_id' => $teacher->id,
                'status' => 'ongoing',
                'scheduled_at' => now()->subDays(7),
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(7),
                'duration' => 60,
                'total_points' => 0,
            ]);

            $evaluations[] = $evaluation;
        }

        // =====================================================
        // 8. QUESTÕES POR AVALIAÇÃO (8 a 12) ALINHADAS ÀS CAPACIDADES
        // =====================================================

        // Templates de questões por capacity_code
        $templatesByCapacity = [
            'C1' => [
                [
                    'statement' => 'Qual documento é mais adequado para registrar uma comunicação formal entre setores?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Memorando',                 'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Bilhete informal',          'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Mensagem de WhatsApp',      'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Conversas verbais apenas',  'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual documento é mais adequado para registrar decisões de reuniões?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Ata',                      'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Ofício',                   'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Recibo',                   'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Carta pessoal',            'is_correct' => false],
                    ],
                ],
            ],
            'C2' => [
                [
                    'statement' => 'Em um arquivo organizado alfabeticamente, onde deve ser guardado o prontuário de “Ana Souza”?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Na seção S, em Souza, Ana', 'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Na seção A, em Ana',        'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Junto com qualquer “A”',    'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Não importa a posição',     'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual ferramenta é mais adequada para arquivamento eficiente?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Pasta suspensa',            'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Grampeador',               'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Calculadora',              'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Régua',                    'is_correct' => false],
                    ],
                ],
            ],
            'C26' => [
                [
                    'statement' => 'Qual das teorias administrativas enfatiza a estrutura organizacional?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Teoria Clássica',                'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Teoria das Relações Humanas',    'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Teoria da Contingência',         'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Teoria Comportamental',          'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual teoria administrativa foca no comportamento humano e na motivação?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Teoria Clássica',                'is_correct' => false],
                        ['letter' => 'B', 'text' => 'Teoria das Relações Humanas',    'is_correct' => true],
                        ['letter' => 'C', 'text' => 'Teoria Estruturalista',         'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Teoria Burocrática',            'is_correct' => false],
                    ],
                ],
            ],
            'C5' => [
                [
                    'statement' => 'Em usinagem, qual ferramenta é utilizada para realizar o corte principal na peça?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Serra manual',    'is_correct' => false],
                        ['letter' => 'B', 'text' => 'Ferramenta de corte', 'is_correct' => true],
                        ['letter' => 'C', 'text' => 'Martelo',         'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Lixa',            'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual ferramenta é mais adequada para operações de torneamento?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Ferramenta de torneamento', 'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Broca manual',              'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Alicate de pressão',        'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Esmeril de bancada',        'is_correct' => false],
                    ],
                ],
            ],
            'C24' => [
                [
                    'statement' => 'Em um desenho técnico, a vista frontal normalmente representa:',
                    'options' => [
                        ['letter' => 'A', 'text' => 'A peça vista de cima',      'is_correct' => false],
                        ['letter' => 'B', 'text' => 'A peça vista de frente',    'is_correct' => true],
                        ['letter' => 'C', 'text' => 'A peça em perspectiva',     'is_correct' => false],
                        ['letter' => 'D', 'text' => 'A peça em corte total',     'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual projeção é mais utilizada em desenhos técnicos mecânicos no Brasil?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Primeiro diedro',          'is_correct' => true],
                        ['letter' => 'B', 'text' => 'Terceiro diedro',          'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Perspectiva isométrica',   'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Axonométrica oblíqua',     'is_correct' => false],
                    ],
                ],
            ],
            'C39' => [
                [
                    'statement' => 'Qual componente eletrônico é utilizado para armazenar carga elétrica?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Resistor',    'is_correct' => false],
                        ['letter' => 'B', 'text' => 'Capacitor',   'is_correct' => true],
                        ['letter' => 'C', 'text' => 'Indutor',     'is_correct' => false],
                        ['letter' => 'D', 'text' => 'Diodo',       'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'O que o código de cores em um resistor indica?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'Tensão máxima',         'is_correct' => false],
                        ['letter' => 'B', 'text' => 'Corrente máxima',       'is_correct' => false],
                        ['letter' => 'C', 'text' => 'Valor da resistência',  'is_correct' => true],
                        ['letter' => 'D', 'text' => 'Tipo de material',      'is_correct' => false],
                    ],
                ],
            ],
            'C12' => [
                [
                    'statement' => 'Qual estrutura condicional é mais adequada para múltiplas escolhas fixas?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'if/else',  'is_correct' => false],
                        ['letter' => 'B', 'text' => 'while',    'is_correct' => false],
                        ['letter' => 'C', 'text' => 'switch',   'is_correct' => true],
                        ['letter' => 'D', 'text' => 'for',      'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Em programação, qual estrutura é mais adequada para repetir um bloco enquanto uma condição for verdadeira?',
                    'options' => [
                        ['letter' => 'A', 'text' => 'if',       'is_correct' => false],
                        ['letter' => 'B', 'text' => 'switch',   'is_correct' => false],
                        ['letter' => 'C', 'text' => 'for',      'is_correct' => false],
                        ['letter' => 'D', 'text' => 'while',    'is_correct' => true],
                    ],
                ],
            ],
        ];

        $allQuestions = [];

        foreach ($evaluations as $evaluation) {
            $class = $evaluation->schoolClass;
            $course = $class->course;

            // Capacidades associadas ao curso
            $courseCapacities = Capacity::whereHas('courses', function ($q) use ($course) {
                $q->where('courses.id', $course->id);
            })->get()->filter(function ($cap) use ($templatesByCapacity) {
                return isset($templatesByCapacity[$cap->code]);
            })->values();

            if ($courseCapacities->isEmpty()) {
                continue;
            }

            $numQuestions = random_int(8, 12);

            for ($i = 0; $i < $numQuestions; $i++) {
                $capacity = $courseCapacities->random();
                $templates = $templatesByCapacity[$capacity->code];
                $template = $templates[array_rand($templates)];

                /** @var Question $question */
                $question = $evaluation->questions()->create([
                    'statement'   => $template['statement'],
                    'type'        => 'multiple_choice',
                    'points'      => 10,
                    'capacity_id' => $capacity->id,
                ]);

                $question->options()->createMany($template['options']);
                $allQuestions[] = $question;
            }

            // Atualiza total_points da avaliação
            $evaluation->total_points = $evaluation->questions()->sum('points');
            $evaluation->save();
        }

        // =====================================================
        // 9. GERAR TENTATIVAS (UserQuestionAttempt + Answer + QuestionStatistic)
        // =====================================================

        // índice rápido: class_id => [student_ids...]
        // (já montado em $classStudents)

        foreach ($allQuestions as $question) {
            $evaluation = $question->evaluation;
            $classId = $evaluation->school_class_id;

            $studentsIds = $classStudents[$classId] ?? [];
            if (empty($studentsIds)) {
                continue;
            }

            // garante que exista uma linha de estatística
            /** @var QuestionStatistic $stats */
            $stats = $question->statistics()->firstOrCreate([
                'question_id' => $question->id,
            ]);

            $options = $question->options()->get();
            $correctOption = $options->firstWhere('is_correct', true);
            $incorrectOptions = $options->where('is_correct', false)->values();

            foreach ($studentsIds as $studentId) {
                // 70% de chance do aluno responder a esta questão
                if (random_int(1, 100) > 70) {
                    continue;
                }

                $answerCorrect = random_int(1, 100) <= 60 && $correctOption;

                if ($answerCorrect && $correctOption) {
                    $chosenOption = $correctOption;
                } else {
                    if ($incorrectOptions->isNotEmpty()) {
                        $chosenOption = $incorrectOptions->random();
                    } else {
                        $chosenOption = $correctOption; // fallback
                        $answerCorrect = true;
                    }
                }

                $answeredAt = now()->subMinutes(random_int(10, 120));

                // cria ANSWER
                $answer = Answer::create([
                    'question_id'    => $question->id,
                    'user_id'        => $studentId,
                    'answer_content' => $chosenOption->id, // mesmo padrão do controller
                    'score'          => $answerCorrect ? ($question->points ?? 10) : 0,
                    'is_correct'     => $answerCorrect,
                    'answered_at'    => $answeredAt,
                    'time_spent'     => random_int(20, 300),
                ]);

                // cria tentativa única
                $attempt = UserQuestionAttempt::create([
                    'user_id'           => $studentId,
                    'question_id'       => $question->id,
                    'answer_id'         => $answer->id,
                    'is_correct'        => $answerCorrect,
                    'selected_option'   => $chosenOption->letter,
                    'first_answered_at' => $answeredAt,
                    'last_answered_at'  => $answeredAt,
                    'attempt_count'     => 1,
                ]);

                // atualiza estatísticas (usa a mesma lógica do modelo)
                $stats->updateStats($answerCorrect, $chosenOption->letter);
            }
        }

        $this->command->info('✅ Ambiente DEV completo criado: roles, competências, regionais, UOs, cursos, turmas, usuários, avaliações, questões e estatísticas.');
    }
}
