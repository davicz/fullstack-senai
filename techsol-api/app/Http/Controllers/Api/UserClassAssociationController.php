<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserClassAssociationController extends Controller
{
    /**
     * Lista as turmas associadas a um usuário específico.
     */
    public function index(User $user)
    {
        // Adicionar lógica de permissão aqui se necessário
        return $user->classes()->with('course', 'operationalUnit')->get();
    }

    /**
     * Sincroniza (atualiza) a lista de turmas de um usuário.
     */
    public function sync(Request $request, User $user)
    {
        $loggedInUser = Auth::user()->load('roles');

        // Lógica de permissão: Apenas admins podem alterar associações.
        if (!$loggedInUser->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id'
        ]);

        // O método sync apaga as matrículas antigas e insere as novas. Perfeito para edição!
        $user->classes()->sync($validatedData['class_ids']);

        return response()->json([
            'message' => 'Associações de turma do usuário atualizadas com sucesso!',
            'user' => $user->load('classes')
        ]);
    }
}