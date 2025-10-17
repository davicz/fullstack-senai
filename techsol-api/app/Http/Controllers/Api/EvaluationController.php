<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Lista as avaliações com base nas permissões do utilizador.
     */
    public function index()
        {
            $user = Auth::user();

            // Admin vê tudo
            if ($user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
                return Evaluation::with('schoolClass.course')->orderBy('created_at', 'desc')->get();
            }

            // Docente vê as avaliações que criou
            if ($user->roles->contains('slug', 'teacher')) {
                return Evaluation::where('created_by_user_id', $user->id)
                    ->with('schoolClass.course')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Aluno vê as avaliações das turmas em que está matriculado
            if ($user->roles->contains('slug', 'student')) {
                // 1. Pega os IDs de todas as turmas em que o aluno está
                $classIds = $user->classes()->pluck('school_class_id');

                // 2. Busca todas as avaliações que pertencem a essas turmas
                return Evaluation::whereIn('school_class_id', $classIds)
                    ->with('schoolClass.course')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

    /**
     * Cria uma nova avaliação.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Apenas Docentes podem criar avaliações por esta rota
        if (!$user->roles->contains('slug', 'teacher')) {
            return response()->json(['message' => 'Apenas docentes podem criar avaliações.'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'school_class_id' => 'required|exists:classes,id',
            'scheduled_at' => 'nullable|date',
        ]);
        
        // Validação de Permissão: O docente está associado a esta turma?
        $isAssociatedWithClass = $user->classes()->where('school_class_id', $validatedData['school_class_id'])->exists();
        
        if (!$isAssociatedWithClass) {
            return response()->json(['message' => 'Permissão negada. Você não está associado a esta turma.'], 403);
        }

        $evaluation = Evaluation::create([
            'title' => $validatedData['title'],
            'type' => $validatedData['type'],
            'school_class_id' => $validatedData['school_class_id'],
            'created_by_user_id' => $user->id,
            'scheduled_at' => $validatedData['scheduled_at'] ?? null,
            'status' => 'draft', // Toda avaliação começa como um rascunho
        ]);

        return response()->json($evaluation, 201);
    }

    public function show(Evaluation $evaluation)
    {
        $user = Auth::user();

        // Lógica de Permissão (a mesma de antes)
        $isCreator = $evaluation->created_by_user_id === $user->id;
        $isEnrolledStudent = $user->roles->contains('slug', 'student') &&
                            $user->classes()->where('school_class_id', $evaluation->school_class_id)->exists();

        if (!$isCreator && !$isEnrolledStudent) {
            return response()->json(['message' => 'Acesso não autorizado a esta avaliação.'], 403);
        }

        // --- A MUDANÇA ESTÁ AQUI ---
        // Se o utilizador for um aluno, carregamos também as suas respostas.
        if ($isEnrolledStudent) {
            $evaluation->load(['questions.options', 'questions.answers' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }]);
        } else {
            // Se for o docente, carregamos as perguntas e opções, mas não filtramos as respostas (ainda)
            $evaluation->load('questions.options');
        }

        return response()->json($evaluation);
    }
}