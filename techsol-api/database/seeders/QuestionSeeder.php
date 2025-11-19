<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Capacity;
use App\Models\Evaluation;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Pegamos a primeira avaliação
        $evaluation = Evaluation::first();

        if (!$evaluation) {
            $this->command->error("Nenhuma avaliação encontrada. Crie uma antes de rodar este seeder.");
            return;
        }

        // 2) Pegamos algumas capacidades existentes
        $capacities = Capacity::limit(5)->get();

        if ($capacities->isEmpty()) {
            $this->command->error("Nenhuma capacidade encontrada. Rode o CompetenciesSeeder primeiro.");
            return;
        }

        // 3) Questões exemplo para cada capacidade
        $templateQuestions = [
            [
                'statement' => 'Qual das teorias administrativas enfatiza a estrutura organizacional?',
                'options' => [
                    ['letter' => 'A', 'text' => 'Teoria Clássica', 'is_correct' => true],
                    ['letter' => 'B', 'text' => 'Teoria das Relações Humanas', 'is_correct' => false],
                    ['letter' => 'C', 'text' => 'Teoria da Contingência', 'is_correct' => false],
                    ['letter' => 'D', 'text' => 'Teoria Comportamental', 'is_correct' => false],
                ],
            ],
            [
                'statement' => 'Qual ferramenta é mais adequada para arquivamento eficiente?',
                'options' => [
                    ['letter' => 'A', 'text' => 'Pasta suspensa', 'is_correct' => true],
                    ['letter' => 'B', 'text' => 'Grampeador', 'is_correct' => false],
                    ['letter' => 'C', 'text' => 'Calculadora', 'is_correct' => false],
                    ['letter' => 'D', 'text' => 'Régua', 'is_correct' => false],
                ],
            ],
            [
                'statement' => 'Em usinagem, qual ferramenta é utilizada para corte?',
                'options' => [
                    ['letter' => 'A', 'text' => 'Serra manual', 'is_correct' => false],
                    ['letter' => 'B', 'text' => 'Ferramenta de corte', 'is_correct' => true],
                    ['letter' => 'C', 'text' => 'Martelo', 'is_correct' => false],
                    ['letter' => 'D', 'text' => 'Lixa', 'is_correct' => false],
                ],
            ],
            [
                'statement' => 'Qual componente eletrônico é utilizado para armazenar carga elétrica?',
                'options' => [
                    ['letter' => 'A', 'text' => 'Resistor', 'is_correct' => false],
                    ['letter' => 'B', 'text' => 'Capacitor', 'is_correct' => true],
                    ['letter' => 'C', 'text' => 'Indutor', 'is_correct' => false],
                    ['letter' => 'D', 'text' => 'Diodo', 'is_correct' => false],
                ],
            ],
            [
                'statement' => 'Qual estrutura condicional é usada para múltiplas escolhas?',
                'options' => [
                    ['letter' => 'A', 'text' => 'if/else', 'is_correct' => false],
                    ['letter' => 'B', 'text' => 'while', 'is_correct' => false],
                    ['letter' => 'C', 'text' => 'switch', 'is_correct' => true],
                    ['letter' => 'D', 'text' => 'for', 'is_correct' => false],
                ],
            ],
        ];

        // 4) Criar questões vinculando a capacidade correta
        foreach ($capacities as $i => $capacity) {
            
            $template = $templateQuestions[$i % count($templateQuestions)];

            // Criar a questão
            $question = Question::create([
                'evaluation_id' => $evaluation->id,
                'capacity_id' => $capacity->id,
                'statement' => $template['statement'],
                'type' => 'multiple_choice',
                'points' => 10,
            ]);

            // Criar opções
            foreach ($template['options'] as $opt) {
                $question->options()->create($opt);
            }
        }

        $this->command->info("✅ QuestionsSeeder rodou com sucesso!");
    }
}
