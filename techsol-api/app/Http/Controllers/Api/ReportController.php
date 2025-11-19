<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Relatório de Desempenho por Item
     * Lista questões com estatísticas completas
     */
    public function performanceByItem(Request $request)
    {
        $user = Auth::user()->load('roles');

        // Apenas admins e teachers podem acessar relatórios
        if (!$user->roles->contains(fn($role) => 
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $query = Question::with([
            'capacity.performanceStandard.subfunction.function.knowledge',
            'capacity.courses',
            'statistics',
            'options' => fn($q) => $q->orderBy('letter'),
            'evaluation.schoolClass.course', // ★ Adicionado: curso verdadeiro
        ])->whereHas('capacity');

        // ==========================================
        // FILTROS
        // ==========================================

        if ($request->has('itinerario')) {
            $query->whereYear('created_at', $request->itinerario);
        }

        if ($request->has('identificador')) {
            $id = str_replace('SIAC_', '', $request->identificador);
            $query->where('id', $id);
        }

        if ($request->has('course_id')) {
            $query->whereHas('evaluation.schoolClass.course', function($q) use ($request) {
                $q->where('courses.id', $request->course_id);
            });
        }

        if ($request->has('subfunction_code')) {
            $query->whereHas('capacity.performanceStandard.subfunction', fn($q) =>
                $q->where('code', $request->subfunction_code)
            );
        }

        if ($request->has('performance_standard_code')) {
            $query->whereHas('capacity.performanceStandard', fn($q) =>
                $q->where('code', $request->performance_standard_code)
            );
        }

        if ($request->has('capacity_code')) {
            $query->whereHas('capacity', fn($q) =>
                $q->where('code', $request->capacity_code)
            );
        }

        if ($request->has('difficulty')) {
            $query->whereHas('statistics', fn($q) =>
                $q->where('difficulty_level', $request->difficulty)
            );
        }

        if ($request->has('min_responses')) {
            $query->whereHas('statistics', fn($q) =>
                $q->where('total_responses', '>=', $request->min_responses)
            );
        }

        if ($request->has('gabarito')) {
            $query->whereHas('options', function($q) use ($request) {
                $q->where('is_correct', true)
                  ->where('letter', $request->gabarito);
            });
        }

        // ==========================================
        // PERMISSÕES POR PERFIL
        // ==========================================

        if ($user->roles->contains('slug', 'regional_admin')) {
            $unitIds = $user->regionalDepartment->operationalUnits()->pluck('id');
            $query->whereHas('evaluation.schoolClass', fn($q) =>
                $q->whereIn('operational_unit_id', $unitIds)
            );
        }

        if ($user->roles->contains('slug', 'unit_admin')) {
            $query->whereHas('evaluation.schoolClass', fn($q) => 
                $q->where('operational_unit_id', $user->operational_unit_id)
            );
        }

        if ($user->roles->contains('slug', 'teacher')) {
            $query->whereHas('evaluation', fn($q) =>
                $q->where('created_by_user_id', $user->id)
            );
        }

        // ==========================================
        // BUSCA TEXTUAL
        // ==========================================
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('statement', 'like', "%{$request->search}%")
                  ->orWhere('id', 'like', "%{$request->search}%");
            });
        }

        // ==========================================
        // ORDENAÇÃO
        // ==========================================
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'responses':
                $query->leftJoin('question_statistics', 'questions.id', '=', 'question_statistics.question_id')
                    ->orderBy('question_statistics.total_responses', $sortOrder)
                    ->select('questions.*');
                break;

            case 'accuracy':
                $query->leftJoin('question_statistics', 'questions.id', '=', 'question_statistics.question_id')
                    ->orderBy('question_statistics.accuracy_rate', $sortOrder)
                    ->select('questions.*');
                break;

            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        // ==========================================
        // PAGINAÇÃO
        // ==========================================
        $perPage = $request->get('per_page', 50);
        $questions = $query->paginate($perPage);

        // ==========================================
        // FORMATAR RESPOSTA
        // ==========================================
        $formatted = $questions->map(function($question) {

            $hierarchy = $question->capacity->getFullHierarchy();
            $stats = $question->statistics ?? null;

            // ★ Curso real
            $course = $question->evaluation->schoolClass->course ?? null;

            // ★ Estatísticas seguras
            $total = $stats->total_responses ?? 0;

            return [
                'id' => $question->id,
                'itinerario' => $question->created_at->format('Y'),
                'identificador' => 'SIAC_' . $question->id,

                // ★ CURSO CORRIGIDO
                'curso' => $course->name ?? '—',
                'curso_id' => $course->id ?? null,

                // Competências
                'subfunction' => [
                    'code' => $hierarchy['subfunction']->code,
                    'name' => $hierarchy['subfunction']->name,
                ],
                'performance_standard' => [
                    'code' => $hierarchy['performance_standard']->code,
                    'name' => $hierarchy['performance_standard']->name,
                ],
                'capacity' => [
                    'code' => $hierarchy['capacity']->code,
                    'name' => $hierarchy['capacity']->name,
                ],

                'full_hierarchy' => $hierarchy,

                // ★ Dificuldade corrigida
                'difficulty_level' =>
                    $stats && $stats->difficulty_level
                        ? $this->translateDifficulty($stats->difficulty_level)
                        : '—',

                // Estatísticas
                'total_responses' => $stats->total_responses ?? 0,
                'correct_answers' => $stats->correct_answers ?? 0,
                'wrong_answers' => $stats->wrong_answers ?? 0,
                'accuracy_rate' => round($stats->accuracy_rate ?? 0, 2),
                'error_rate' => round(100 - ($stats->accuracy_rate ?? 0), 2),

                // Gabarito
                'correct_answer' => $question->getCorrectAnswerLetter(),

                // Distribuição
                'option_distribution' =>
                    $stats ? $stats->getOptionPercentages() : ['A'=>0,'B'=>0,'C'=>0,'D'=>0,'E'=>0],

                // Questão
                'statement' => $question->statement,
                'options' => $question->options->map(fn($opt) => [
                    'letter' => $opt->letter,
                    'text' => $opt->text,
                    'is_correct' => $opt->is_correct,
                ]),
            ];
        });

        return response()->json([
            'data' => $formatted,
            'meta' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
            ]
        ]);
    }

    /**
     * Traduz níveis de dificuldade
     */
    private function translateDifficulty($level)
    {
        $translations = [
            'muito_facil' => 'Muito Fácil',
            'facil' => 'Fácil',
            'medio' => 'Médio',
            'dificil' => 'Difícil',
            'muito_dificil' => 'Muito Difícil',
        ];

        return $translations[$level] ?? '—';
    }

    /**
     * Detalhes completos da questão
     */
    public function questionDetails(Question $question)
    {
        $user = Auth::user()->load('roles');

        if (!$this->canAccessQuestion($user, $question)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $question->load([
            'capacity.performanceStandard.subfunction.function.knowledge',
            'statistics',
            'options',
            'attempts.user'
        ]);

        $hierarchy = $question->capacity->getFullHierarchy();
        $stats = $question->statistics ?? new QuestionStatistic();

        return response()->json([
            'question' => [
                'id' => $question->id,
                'identifier' => 'SIAC_' . $question->id,
                'statement' => $question->statement,
                'type' => $question->type,
                'created_at' => $question->created_at->format('d/m/Y'),
            ],
            'hierarchy' => [
                'knowledge' => $hierarchy['knowledge'],
                'function' => $hierarchy['function'],
                'subfunction' => $hierarchy['subfunction'],
                'performance_standard' => $hierarchy['performance_standard'],
                'capacity' => $hierarchy['capacity'],
            ],
            'statistics' => [
                'total_responses' => $stats->total_responses ?? 0,
                'correct_answers' => $stats->correct_answers ?? 0,
                'wrong_answers' => $stats->wrong_answers ?? 0,
                'accuracy_rate' => round($stats->accuracy_rate ?? 0, 2),
                'difficulty' =>
                    $stats->difficulty_level
                        ? $this->translateDifficulty($stats->difficulty_level)
                        : '—',
                'option_distribution' =>
                    $stats->getOptionPercentages(),
            ],
            'options' => $question->options,
            'students_answered' => $question->attempts->map(fn($attempt) => [
                'user_name' => $attempt->user->name,
                'selected_option' => $attempt->selected_option,
                'is_correct' => $attempt->is_correct,
                'answered_at' => optional($attempt->first_answered_at)->format('d/m/Y H:i'),
            ]),
        ]);
    }

    private function canAccessQuestion($user, $question)
    {
        if ($user->roles->contains('slug', 'national_admin')) {
            return true;
        }

        return true;
    }
}
