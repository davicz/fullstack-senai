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

    // 1. Permissão: apenas alunos
    if (!$user->roles->contains('slug', 'student')) {
        return response()->json(['message' => 'Apenas alunos podem submeter respostas.'], 403);
    }

    // Aluno está matriculado na turma da avaliação?
    $isEnrolled = $user->classes()
        ->where('school_class_id', $question->evaluation->school_class_id)
        ->exists();

    if (!$isEnrolled) {
        return response()->json(['message' => 'Você não está matriculado na turma desta avaliação.'], 403);
    }

    // 2. Validação dos Dados
    $validatedData = $request->validate([
        'option_id' => 'nullable|exists:options,id',
        'answer_content' => 'nullable|string',
        'time_spent' => 'nullable|integer|min:0',
    ]);

    // 3. Cria ou atualiza a resposta do aluno para essa questão
    $answerContent = $validatedData['answer_content'] ?? $validatedData['option_id'] ?? null;

    $answer = $question->answers()->updateOrCreate(
        [
            'user_id' => $user->id,
        ],
        [
            'answer_content' => $answerContent,
        ]
    );

    // 4. Cálculo de correção e pontuação
    $isCorrect = false;
    $score = 0;

    if ($question->type === 'multiple_choice' && isset($validatedData['option_id'])) {
        $isCorrect = $question->checkAnswer($validatedData['option_id']);
        $score = $question->calculateScore($validatedData['option_id']);
    }

    $answer->is_correct = $isCorrect;
    $answer->score = $score;
    $answer->answered_at = now();

    if (isset($validatedData['time_spent'])) {
        $answer->time_spent = $validatedData['time_spent'];
    }

    $answer->save(); // <-- aqui dispara o booted() e atualiza progresso por capacidade

    // 5. Atualizar estatísticas agregadas da questão (QuestionStatistic + UserQuestionAttempt)
    $selectedOption = null;

    if ($question->type === 'multiple_choice' && isset($validatedData['option_id'])) {
        $option = $question->options()->find($validatedData['option_id']);
        if ($option) {
            $selectedOption = $option->letter;
        }
    }

    // Registro de tentativa única por usuário/questão
    $attempt = \App\Models\UserQuestionAttempt::updateOrCreate(
        [
            'user_id' => $user->id,
            'question_id' => $question->id,
        ],
        [
            'answer_id' => $answer->id,
            'is_correct' => $isCorrect,
            'selected_option' => $selectedOption,
            'last_answered_at' => now(),
        ]
    );

    if ($attempt->wasRecentlyCreated) {
        $attempt->first_answered_at = now();
        $attempt->save();

        // Atualizar estatísticas da questão
        $stats = $question->statistics()->firstOrCreate(['question_id' => $question->id]);
        $stats->updateStats($isCorrect, $selectedOption);
    }

    return response()->json($answer->fresh(), 201);
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