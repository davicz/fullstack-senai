<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;
use App\Models\Evaluation;

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

    public function update(Request $request, Answer $answer)
    {
        $user = Auth::user();

        // 1. Validação de Permissão
        // Apenas o docente que criou a avaliação pode atribuir notas.
        // Carregamos a relação para aceder à avaliação a partir da resposta.
        $evaluation = $answer->question->evaluation;
        
        if ($evaluation->created_by_user_id !== $user->id) {
            return response()->json(['message' => 'Acesso não autorizado. Apenas o criador da avaliação pode atribuir notas.'], 403);
        }

        // 2. Validação dos Dados
        $validatedData = $request->validate([
            'score' => 'required|numeric|min:0|max:100', // Exemplo: nota de 0 a 100
        ]);

        // 3. Atualiza a Resposta
        $answer->score = $validatedData['score'];
        $answer->save();

        return response()->json($answer);
    }

    public function index(Evaluation $evaluation)
    {
        $user = Auth::user();

        // Se o utilizador logado for o docente que criou a avaliação, mostra todas as respostas
        if ($user->id === $evaluation->created_by_user_id) {
            // Carrega as respostas, incluindo a informação do utilizador (aluno) e da questão
            $answers = $evaluation->answers()->with(['user', 'question'])->get();
            
            // Agrupa as respostas por utilizador para ser mais fácil de ler no frontend
            return $answers->groupBy('user_id');
        }

        // Se o utilizador logado for um aluno, mostra apenas as suas respostas para esta avaliação
        if ($user->roles->contains('slug', 'student')) {
            return $evaluation->answers()->where('user_id', $user->id)->with(['user', 'question'])->get();
        }

        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }
}