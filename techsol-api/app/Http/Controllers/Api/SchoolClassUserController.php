<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassUserController extends Controller
{
    /**
     * Associa um ou mais utilizadores a uma turma específica.
     */
    public function store(Request $request, SchoolClass $schoolClass)
    {
        $user = Auth::user()->load('roles');

        // Valida que recebemos um array de IDs de utilizador
        $validatedData = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id', // Garante que todos os IDs de utilizador são válidos
        ]);

        // Lógica de Permissão (semelhante à criação de turmas)
        if ($user->roles->contains('slug', 'regional_admin')) {
            if (!$user->regionalDepartment->operationalUnits()->where('id', $schoolClass->operational_unit_id)->exists()) {
                return response()->json(['message' => 'Permissão para gerir matrículas apenas em UOs do seu DR.'], 403);
            }
        } else if ($user->roles->contains('slug', 'unit_admin')) {
            if ($user->operational_unit_id != $schoolClass->operational_unit_id) {
                return response()->json(['message' => 'Permissão para gerir matrículas apenas na sua própria UO.'], 403);
            }
        } else if (!$user->roles->contains('slug', 'national_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        // Usa o método attach() para adicionar os novos utilizadores à turma,
        // sem remover os que já estavam matriculados.
        $schoolClass->users()->attach($validatedData['user_ids']);

        return response()->json([
            'message' => 'Utilizadores associados à turma com sucesso!',
            'class' => $schoolClass->load('users') // Retorna a turma com a lista de utilizadores atualizada
        ]);
    }
}