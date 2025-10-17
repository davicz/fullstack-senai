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

        // Lógica de permissão (exemplo): Apenas Admins veem tudo, Docentes veem as suas.
        if ($user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return Evaluation::with('schoolClass')->get();
        }

        if ($user->roles->contains('slug', 'teacher')) {
            return Evaluation::where('created_by_user_id', $user->id)
                ->with('schoolClass')
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
}