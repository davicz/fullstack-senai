<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competency;
use Illuminate\Support\Facades\DB;

class CompetencySeeder extends Seeder
{
    /**
     * Seed competências baseadas no modelo SENAI
     * 
     * Estrutura hierárquica:
     * - Competências Gerais (nível 1)
     *   - Competências Específicas (nível 2-3)
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Competency::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // COMPETÊNCIAS GERAIS (Transversais)
        // ========================================
        
        $comunicacao = Competency::create([
            'code' => 'CG-001',
            'name' => 'Comunicação',
            'description' => 'Capacidade de comunicar-se de forma clara e efetiva em diferentes contextos profissionais',
            'level' => 1,
            'is_active' => true,
        ]);

        $trabalhoEquipe = Competency::create([
            'code' => 'CG-002',
            'name' => 'Trabalho em Equipe',
            'description' => 'Habilidade de trabalhar colaborativamente com outros profissionais',
            'level' => 1,
            'is_active' => true,
        ]);

        $resolucaoProblemas = Competency::create([
            'code' => 'CG-003',
            'name' => 'Resolução de Problemas',
            'description' => 'Capacidade de identificar, analisar e resolver problemas complexos',
            'level' => 1,
            'is_active' => true,
        ]);

        $pensamentoCritico = Competency::create([
            'code' => 'CG-004',
            'name' => 'Pensamento Crítico',
            'description' => 'Habilidade de analisar informações de forma crítica e tomar decisões fundamentadas',
            'level' => 1,
            'is_active' => true,
        ]);

        $eticaProfissional = Competency::create([
            'code' => 'CG-005',
            'name' => 'Ética Profissional',
            'description' => 'Comprometimento com valores éticos e responsabilidade profissional',
            'level' => 1,
            'is_active' => true,
        ]);

        // ========================================
        // SUB-COMPETÊNCIAS DE COMUNICAÇÃO
        // ========================================
        
        Competency::create([
            'code' => 'CG-001.1',
            'name' => 'Comunicação Oral',
            'description' => 'Expressar-se verbalmente com clareza e objetividade',
            'parent_id' => $comunicacao->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'CG-001.2',
            'name' => 'Comunicação Escrita',
            'description' => 'Elaborar documentos técnicos e relatórios profissionais',
            'parent_id' => $comunicacao->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'CG-001.3',
            'name' => 'Comunicação Visual',
            'description' => 'Interpretar e criar representações gráficas e diagramas',
            'parent_id' => $comunicacao->id,
            'level' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // COMPETÊNCIAS TÉCNICAS - INFORMÁTICA
        // ========================================
        
        $programacao = Competency::create([
            'code' => 'TI-001',
            'name' => 'Programação de Computadores',
            'description' => 'Desenvolver sistemas e aplicações utilizando linguagens de programação',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'TI-001.1',
            'name' => 'Lógica de Programação',
            'description' => 'Aplicar raciocínio lógico para resolução de problemas computacionais',
            'parent_id' => $programacao->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'TI-001.2',
            'name' => 'Estruturas de Dados',
            'description' => 'Utilizar estruturas de dados apropriadas para organização de informações',
            'parent_id' => $programacao->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'TI-001.3',
            'name' => 'Desenvolvimento Web',
            'description' => 'Criar aplicações web utilizando tecnologias front-end e back-end',
            'parent_id' => $programacao->id,
            'level' => 3,
            'is_active' => true,
        ]);

        $bancoDados = Competency::create([
            'code' => 'TI-002',
            'name' => 'Banco de Dados',
            'description' => 'Projetar, implementar e gerenciar sistemas de banco de dados',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'TI-002.1',
            'name' => 'Modelagem de Dados',
            'description' => 'Criar modelos conceituais, lógicos e físicos de banco de dados',
            'parent_id' => $bancoDados->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'TI-002.2',
            'name' => 'SQL',
            'description' => 'Utilizar linguagem SQL para manipulação e consulta de dados',
            'parent_id' => $bancoDados->id,
            'level' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // COMPETÊNCIAS TÉCNICAS - MECÂNICA
        // ========================================
        
        $desenhoTecnico = Competency::create([
            'code' => 'MEC-001',
            'name' => 'Desenho Técnico',
            'description' => 'Interpretar e elaborar desenhos técnicos mecânicos',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'MEC-001.1',
            'name' => 'Leitura de Desenho',
            'description' => 'Interpretar representações gráficas de peças e conjuntos mecânicos',
            'parent_id' => $desenhoTecnico->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'MEC-001.2',
            'name' => 'Projeção Ortogonal',
            'description' => 'Representar objetos tridimensionais em vistas ortogonais',
            'parent_id' => $desenhoTecnico->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $usinagem = Competency::create([
            'code' => 'MEC-002',
            'name' => 'Processos de Usinagem',
            'description' => 'Executar operações de usinagem em máquinas convencionais e CNC',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'MEC-002.1',
            'name' => 'Operação de Torno',
            'description' => 'Operar torno mecânico para fabricação de peças cilíndricas',
            'parent_id' => $usinagem->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'MEC-002.2',
            'name' => 'Operação de Fresadora',
            'description' => 'Operar fresadora para confecção de superfícies planas e canais',
            'parent_id' => $usinagem->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'MEC-002.3',
            'name' => 'Programação CNC',
            'description' => 'Programar máquinas CNC utilizando código G e M',
            'parent_id' => $usinagem->id,
            'level' => 3,
            'is_active' => true,
        ]);

        // ========================================
        // COMPETÊNCIAS TÉCNICAS - ELETRÔNICA
        // ========================================
        
        $circuitosEletronicos = Competency::create([
            'code' => 'ELE-001',
            'name' => 'Circuitos Eletrônicos',
            'description' => 'Analisar, montar e testar circuitos eletrônicos',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ELE-001.1',
            'name' => 'Componentes Eletrônicos',
            'description' => 'Identificar e aplicar componentes eletrônicos básicos',
            'parent_id' => $circuitosEletronicos->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ELE-001.2',
            'name' => 'Análise de Circuitos',
            'description' => 'Analisar circuitos utilizando leis e teoremas fundamentais',
            'parent_id' => $circuitosEletronicos->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $microcontroladores = Competency::create([
            'code' => 'ELE-002',
            'name' => 'Microcontroladores',
            'description' => 'Programar e aplicar microcontroladores em sistemas embarcados',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ELE-002.1',
            'name' => 'Programação Arduino',
            'description' => 'Desenvolver projetos utilizando plataforma Arduino',
            'parent_id' => $microcontroladores->id,
            'level' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // COMPETÊNCIAS TÉCNICAS - ADMINISTRAÇÃO
        // ========================================
        
        $gestaoProcessos = Competency::create([
            'code' => 'ADM-001',
            'name' => 'Gestão de Processos',
            'description' => 'Analisar e otimizar processos organizacionais',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ADM-001.1',
            'name' => 'Mapeamento de Processos',
            'description' => 'Documentar e representar graficamente processos de negócio',
            'parent_id' => $gestaoProcessos->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ADM-001.2',
            'name' => 'Indicadores de Desempenho',
            'description' => 'Definir e acompanhar KPIs para gestão de processos',
            'parent_id' => $gestaoProcessos->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $gestaoFinanceira = Competency::create([
            'code' => 'ADM-002',
            'name' => 'Gestão Financeira',
            'description' => 'Gerenciar recursos financeiros e elaborar análises econômicas',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'ADM-002.1',
            'name' => 'Análise Financeira',
            'description' => 'Interpretar demonstrativos e indicadores financeiros',
            'parent_id' => $gestaoFinanceira->id,
            'level' => 2,
            'is_active' => true,
        ]);

        // ========================================
        // COMPETÊNCIAS TÉCNICAS - SEGURANÇA DO TRABALHO
        // ========================================
        
        $segurancaTrabalho = Competency::create([
            'code' => 'SEG-001',
            'name' => 'Segurança do Trabalho',
            'description' => 'Aplicar normas e práticas de segurança no ambiente de trabalho',
            'level' => 1,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'SEG-001.1',
            'name' => 'Equipamentos de Proteção',
            'description' => 'Utilizar corretamente EPIs e EPCs',
            'parent_id' => $segurancaTrabalho->id,
            'level' => 2,
            'is_active' => true,
        ]);

        Competency::create([
            'code' => 'SEG-001.2',
            'name' => 'Prevenção de Acidentes',
            'description' => 'Identificar riscos e implementar medidas preventivas',
            'parent_id' => $segurancaTrabalho->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $this->command->info('✅ ' . Competency::count() . ' competências criadas com sucesso!');
        $this->command->info('📊 Estrutura hierárquica:');
        $this->command->info('   - Competências raiz: ' . Competency::root()->count());
        $this->command->info('   - Sub-competências: ' . Competency::whereNotNull('parent_id')->count());
    }
}