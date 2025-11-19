<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use App\Models\UserCompetencyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetencyController extends Controller
{
    /**
     * Lista todas as competências
     */
    public function index(Request $request)
    {
        $query = Competency::query()->with(['parent', 'children']);

        // Filtrar por curso se fornecido
        if ($request->has('course_id')) {
            $query->whereHas('courses', function ($q) use ($request) {
                $q->where('courses.id', $request->course_id);
            });
        }

        // Filtrar por nível
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Apenas ativas
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Organizar hierarquicamente
        if ($request->boolean('root_only')) {
            $query->root();
        }

        return $query->orderBy('code')->get();
    }

    /**
     * Cria uma nova competência (apenas admins)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'code' => 'required|string|unique:competencies,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:competencies,id',
            'level' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $competency = Competency::create($validatedData);

        return response()->json($competency->load(['parent', 'children']), 201);
    }

    /**
     * Exibe uma competência específica
     */
    public function show(Competency $competency)
    {
        return $competency->load([
            'parent',
            'children',
            'courses',
            'questions'
        ]);
    }

    /**
     * Atualiza uma competência (apenas admins)
     */
    public function update(Request $request, Competency $competency)
    {
        $user = Auth::user();

        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'code' => 'sometimes|string|unique:competencies,code,' . $competency->id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:competencies,id',
            'level' => 'sometimes|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $competency->update($validatedData);

        return response()->json($competency->load(['parent', 'children']));
    }

    /**
     * Remove uma competência (apenas admins)
     */
    public function destroy(Competency $competency)
    {
        $user = Auth::user();

        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $competency->delete();

        return response()->json(['message' => 'Competência excluída com sucesso.']);
    }

    /**
     * Vincula competências a um curso
     */
    public function attachToCourse(Request $request, Competency $competency)
    {
        $user = Auth::user();

        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'weight' => 'integer|min:1|max:100',
            'semester' => 'nullable|integer|min:1',
        ]);

        $competency->courses()->attach($validatedData['course_id'], [
            'weight' => $validatedData['weight'] ?? 100,
            'semester' => $validatedData['semester'] ?? null,
        ]);

        return response()->json(['message' => 'Competência vinculada ao curso com sucesso.']);
    }

    /**
     * Obtém o progresso de um aluno em todas as competências
     */
    public function getStudentProgress(Request $request, $userId = null)
    {
        $user = Auth::user();
        
        // Se não especificou userId, usa o próprio usuário logado
        $targetUserId = $userId ?? $user->id;

        // Verifica permissão
        if ($targetUserId != $user->id && !$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $query = UserCompetencyProgress::where('user_id', $targetUserId)
            ->with(['competency', 'course']);

        // Filtrar por curso
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filtrar por nível de proficiência
        if ($request->has('proficiency_level')) {
            $query->where('proficiency_level', $request->proficiency_level);
        }

        $progress = $query->orderBy('last_evaluated_at', 'desc')->get();

        // Estatísticas gerais
        $stats = [
            'total_competencies' => $progress->count(),
            'average_score' => $progress->avg('score'),
            'proficiency_distribution' => $progress->groupBy('proficiency_level')->map->count(),
            'recent_evaluations' => $progress->where('last_evaluated_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'progress' => $progress,
            'statistics' => $stats,
        ]);
    }

    /**
     * Obtém o ranking de alunos por competência
     */
    public function getCompetencyRanking(Competency $competency, Request $request)
    {
        $user = Auth::user();

        // Apenas admins e teachers podem ver ranking
        if (!$user->roles->contains(fn($role) => 
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $query = UserCompetencyProgress::where('competency_id', $competency->id)
            ->with('user');

        // Filtrar por curso se fornecido
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $ranking = $query->orderBy('score', 'desc')
            ->orderBy('last_evaluated_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'competency' => $competency,
            'ranking' => $ranking,
        ]);
    }
}