<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store(Request $request, Evaluation $evaluation)
    {
        $user = Auth::user();

        if ($evaluation->created_by_user_id !== $user->id) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'statement' => 'required|string',
            'type' => 'required|in:multiple_choice,descriptive',
            'options' => 'sometimes|required_if:type,multiple_choice|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        // Usamos uma transação para garantir que tudo seja criado com sucesso, ou nada é criado.
        $question = DB::transaction(function () use ($evaluation, $validatedData) {
            $question = $evaluation->questions()->create([
                'statement' => $validatedData['statement'],
                'type' => $validatedData['type'],
            ]);

            if (isset($validatedData['options'])) {
                $question->options()->createMany($validatedData['options']);
            }

            return $question;
        });

        return response()->json($question->load('options'), 201); // Retorna a questão com as opções
    }
}