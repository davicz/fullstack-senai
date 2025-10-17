<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * ETAPA 1: Autentica o usuário e retorna os perfis disponíveis ou um token final.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // A LINHA DO ERRO FOI REMOVIDA DAQUI. O Auth::user() JÁ CARREGA OS PERFIS.
        $user = Auth::user();

        // Se o usuário só tem um perfil, loga direto.
        if ($user->roles->count() === 1) {
            $role = $user->roles->first();
            $token = $user->createToken('auth-token', ['user_role_id:' . $role->pivot->id])->plainTextToken;

            return response()->json([
                'message' => 'Login bem-sucedido!',
                'access_token' => $token,
                'user' => $user,
                'selected_role' => $role,
            ]);
        }
        
        // Se tem múltiplos perfis, cria um token temporário só para a seleção.
        $temporaryToken = $user->createToken('temporary-token', ['select-profile'], now()->addMinutes(5))->plainTextToken;

        return response()->json([
            'message' => 'Múltiplos perfis encontrados. Por favor, selecione um.',
            'temporary_token' => $temporaryToken,
            'user' => $user,
        ]);
    }

    /**
     * ETAPA 2: Gera o token final baseado no perfil escolhido.
     */
    public function selectProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_role_id' => 'required|integer',
        ]);

        $selectedRolePivot = $user->roles()->wherePivot('id', $validated['user_role_id'])->first();

        if (!$selectedRolePivot) {
            return response()->json(['message' => 'Perfil inválido ou não pertence a este usuário.'], 403);
        }
        
        $user->tokens()->delete();

        $finalToken = $user->createToken('auth-token', ['role_id:' . $selectedRolePivot->pivot->id])->plainTextToken;

        return response()->json([
            'message' => 'Perfil selecionado com sucesso!',
            'access_token' => $finalToken,
            'user' => $user,
            'selected_role' => $selectedRolePivot,
        ]);
    }
}