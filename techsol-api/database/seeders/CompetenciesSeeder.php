<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Knowledge;
use App\Models\FunctionModel;
use App\Models\Subfunction;
use App\Models\PerformanceStandard;
use App\Models\Capacity;
use Illuminate\Support\Facades\DB;

class CompetenciesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Capacity::truncate();
        PerformanceStandard::truncate();
        Subfunction::truncate();
        FunctionModel::truncate();
        Knowledge::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // CONHECIMENTO 1: Conceitos de Administração
        // ========================================
        $conhec1 = Knowledge::create([
            'code' => 'CON-001',
            'name' => 'Conceitos de administração',
            'description' => 'Fundamentos e teorias da ciência administrativa',
            'is_active' => true,
        ]);

        // FUNÇÃO 1.1
        $func1 = FunctionModel::create([
            'knowledge_id' => $conhec1->id,
            'code' => 'F-001',
            'name' => 'Executar atividades administrativas nos setores de produção e serviços',
            'description' => 'Realizar tarefas administrativas utilizando técnicas e ferramentas apropriadas',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 1.1
        $sub1_1 = Subfunction::create([
            'function_id' => $func1->id,
            'code' => '1.1',
            'name' => 'Realizar atividades de organização e controle',
            'description' => 'Organizar documentos, arquivos e realizar controles administrativos',
            'is_active' => true,
        ]);

        // PADRÃO 1.1.1
        $pad1_1_1 = PerformanceStandard::create([
            'subfunction_id' => $sub1_1->id,
            'code' => '1.1.1',
            'name' => 'Organizar documentos e arquivos',
            'description' => 'Classificar e arquivar documentos seguindo normas e procedimentos',
            'is_active' => true,
        ]);

        // CAPACIDADES do Padrão 1.1.1
        Capacity::create([
            'performance_standard_id' => $pad1_1_1->id,
            'code' => 'C1',
            'name' => 'Identificar tipos de documentos administrativos',
            'description' => 'Reconhecer e classificar diferentes tipos de documentos utilizados na administração',
            'is_active' => true,
        ]);

        Capacity::create([
            'performance_standard_id' => $pad1_1_1->id,
            'code' => 'C2',
            'name' => 'Aplicar técnicas de arquivamento',
            'description' => 'Utilizar métodos de organização e arquivamento de documentos',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 1.4
        $sub1_4 = Subfunction::create([
            'function_id' => $func1->id,
            'code' => '1.4',
            'name' => 'Auxiliar na realização dos processos de recursos humanos (RH)',
            'description' => 'Apoiar as atividades de gestão de pessoas na organização',
            'is_active' => true,
        ]);

        // PADRÃO 1.4.5
        $pad1_4_5 = PerformanceStandard::create([
            'subfunction_id' => $sub1_4->id,
            'code' => '1.4.5',
            'name' => 'Reconhecer teorias administrativas aplicadas ao RH',
            'description' => 'Identificar teorias da administração nos processos de gestão de pessoas',
            'is_active' => true,
        ]);

        // CAPACIDADE C26 (do exemplo)
        Capacity::create([
            'performance_standard_id' => $pad1_4_5->id,
            'code' => 'C26',
            'name' => 'Reconhecer as teorias da administração nos processos administrativos',
            'description' => 'Identificar e aplicar conceitos das principais teorias administrativas',
            'is_active' => true,
        ]);

        // ========================================
        // CONHECIMENTO 2: Processos Mecânicos
        // ========================================
        $conhec2 = Knowledge::create([
            'code' => 'CON-002',
            'name' => 'Processos de fabricação mecânica',
            'description' => 'Conhecimentos sobre usinagem e fabricação de peças',
            'is_active' => true,
        ]);

        // FUNÇÃO 2.1
        $func2 = FunctionModel::create([
            'knowledge_id' => $conhec2->id,
            'code' => 'F-002',
            'name' => 'Executar operações de usinagem',
            'description' => 'Realizar processos de usinagem em máquinas convencionais e CNC',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 1.2
        $sub1_2 = Subfunction::create([
            'function_id' => $func2->id,
            'code' => '1.2',
            'name' => 'Operar máquinas-ferramenta',
            'description' => 'Utilizar tornos, fresas e outras máquinas de usinagem',
            'is_active' => true,
        ]);

        // PADRÃO 1.2.1
        $pad1_2_1 = PerformanceStandard::create([
            'subfunction_id' => $sub1_2->id,
            'code' => '1.2.1',
            'name' => 'Preparar máquina para operação',
            'description' => 'Realizar setup e ajustes necessários na máquina',
            'is_active' => true,
        ]);

        // CAPACIDADES
        Capacity::create([
            'performance_standard_id' => $pad1_2_1->id,
            'code' => 'C5',
            'name' => 'Identificar ferramentas de corte',
            'description' => 'Reconhecer e selecionar ferramentas adequadas para cada operação',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 1.3
        $sub1_3 = Subfunction::create([
            'function_id' => $func2->id,
            'code' => '1.3',
            'name' => 'Interpretar desenhos técnicos',
            'description' => 'Ler e compreender projetos mecânicos',
            'is_active' => true,
        ]);

        // PADRÃO 1.3.3
        $pad1_3_3 = PerformanceStandard::create([
            'subfunction_id' => $sub1_3->id,
            'code' => '1.3.3',
            'name' => 'Reconhecer vistas e projeções',
            'description' => 'Identificar vistas ortogonais e sistemas de projeção',
            'is_active' => true,
        ]);

        // CAPACIDADES
        Capacity::create([
            'performance_standard_id' => $pad1_3_3->id,
            'code' => 'C24',
            'name' => 'Interpretar projeções ortogonais',
            'description' => 'Compreender representação de peças em vistas ortogonais',
            'is_active' => true,
        ]);

        // ========================================
        // CONHECIMENTO 3: Fundamentos de Eletrônica
        // ========================================
        $conhec3 = Knowledge::create([
            'code' => 'CON-003',
            'name' => 'Fundamentos de eletrônica',
            'description' => 'Princípios básicos de circuitos eletrônicos',
            'is_active' => true,
        ]);

        // FUNÇÃO 3.1
        $func3 = FunctionModel::create([
            'knowledge_id' => $conhec3->id,
            'code' => 'F-003',
            'name' => 'Montar e testar circuitos eletrônicos',
            'description' => 'Realizar montagem e testes de circuitos eletrônicos básicos',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 2.1
        $sub2_1 = Subfunction::create([
            'function_id' => $func3->id,
            'code' => '2.1',
            'name' => 'Identificar componentes eletrônicos',
            'description' => 'Reconhecer e especificar componentes eletrônicos',
            'is_active' => true,
        ]);

        // PADRÃO 2.1.6
        $pad2_1_6 = PerformanceStandard::create([
            'subfunction_id' => $sub2_1->id,
            'code' => '2.1.6',
            'name' => 'Reconhecer funções de componentes passivos',
            'description' => 'Identificar resistores, capacitores e indutores',
            'is_active' => true,
        ]);

        // CAPACIDADES
        Capacity::create([
            'performance_standard_id' => $pad2_1_6->id,
            'code' => 'C39',
            'name' => 'Interpretar código de cores de resistores',
            'description' => 'Determinar valor de resistência através do código de cores',
            'is_active' => true,
        ]);

        // ========================================
        // CONHECIMENTO 4: Programação
        // ========================================
        $conhec4 = Knowledge::create([
            'code' => 'CON-004',
            'name' => 'Lógica de programação',
            'description' => 'Fundamentos de algoritmos e programação',
            'is_active' => true,
        ]);

        // FUNÇÃO 4.1
        $func4 = FunctionModel::create([
            'knowledge_id' => $conhec4->id,
            'code' => 'F-004',
            'name' => 'Desenvolver algoritmos e programas',
            'description' => 'Criar soluções computacionais através de programação',
            'is_active' => true,
        ]);

        // SUBFUNÇÃO 3.4
        $sub3_4 = Subfunction::create([
            'function_id' => $func4->id,
            'code' => '3.4',
            'name' => 'Aplicar estruturas de controle',
            'description' => 'Utilizar estruturas condicionais e de repetição',
            'is_active' => true,
        ]);

        // PADRÃO 3.4.X
        $pad3_4_1 = PerformanceStandard::create([
            'subfunction_id' => $sub3_4->id,
            'code' => '3.4.1',
            'name' => 'Implementar estruturas condicionais',
            'description' => 'Usar if, else e switch em algoritmos',
            'is_active' => true,
        ]);

        // CAPACIDADES
        Capacity::create([
            'performance_standard_id' => $pad3_4_1->id,
            'code' => 'C12',
            'name' => 'Reconhecer situações que requerem decisão',
            'description' => 'Identificar quando usar estruturas condicionais',
            'is_active' => true,
        ]);

        $this->command->info('✅ Competências SENAI criadas com sucesso!');
        $this->command->info('📊 Resumo:');
        $this->command->info('   - Conhecimentos: ' . Knowledge::count());
        $this->command->info('   - Funções: ' . FunctionModel::count());
        $this->command->info('   - Subfunções: ' . Subfunction::count());
        $this->command->info('   - Padrões: ' . PerformanceStandard::count());
        $this->command->info('   - Capacidades: ' . Capacity::count());
    }
}