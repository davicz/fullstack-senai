<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    /**
     * Guarda a resposta de um aluno para uma questão específica.
     */
    public function store(Request $request, Question $question)
    {
        $user = Auth::user();

        // 1. Validação de Permissão
        // Apenas alunos podem responder.
        if (!$user->roles->contains('slug', 'student')) {
            return response()->json(['message' => 'Apenas alunos podem submeter respostas.'], 403);
        }

        // O aluno está matriculado na turma desta avaliação?
        $isEnrolled = $user->classes()->where('school_class_id', $question->evaluation->school_class_id)->exists();
        if (!$isEnrolled) {
            return response()->json(['message' => 'Você não está matriculado na turma desta avaliação.'], 403);
        }

        // 2. Validação dos Dados
        $validatedData = $request->validate([
            // A resposta pode ser o ID de uma opção ou um texto livre.
            'option_id' => 'nullable|exists:options,id',
            'answer_content' => 'nullable|string',
        ]);
        
        // 3. Cria a Resposta
        // O método updateOrCreate é útil: se o aluno já respondeu, atualiza a resposta; senão, cria uma nova.
        $answer = $question->answers()->updateOrCreate(
            [
                'user_id' => $user->id, // Chave para encontrar a resposta existente
            ],
            [
                // Dados para criar ou atualizar
                'answer_content' => $validatedData['answer_content'] ?? $validatedData['option_id'],
            ]
        );

        return response()->json($answer, 201);
    }
}