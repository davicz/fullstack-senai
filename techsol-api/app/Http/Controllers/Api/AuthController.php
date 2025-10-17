<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Invitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ETAPA 1: Autentica o usuário e retorna os perfis disponíveis ou um token final.
     */
    public function login(Request $request)
    {
        // --- ESTE BLOCO ESTAVA FALTANDO ---
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];
        // --- FIM DO BLOCO QUE FALTAVA ---

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $user = Auth::user();

        if ($user->roles->isEmpty()) {
            return response()->json(['message' => 'Este usuário não possui perfis de acesso configurados.'], 403);
        }
        
        if ($user->roles->count() === 1) {
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login bem-sucedido!',
                'access_token' => $token,
                'user' => $user,
                'selected_role' => $user->roles->first(),
            ]);
        }
        
        $temporaryToken = $user->createToken('temporary-token', [], now()->addMinutes(5))->plainTextToken;

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

        $finalToken = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Perfil selecionado com sucesso!',
            'access_token' => $finalToken,
            'user' => $user,
            'selected_role' => $selectedRolePivot,
        ]);
    }
    
    /**
     * O MÉTODO DE REGISTRO QUE JÁ ESTÁ CORRETO
     */
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:100',
            'cpf' => 'required|string|size:11|unique:users,cpf',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&].*$/',
        ]);

        $invitation = Invitation::where('token', $request->token)->first();
        if (!$invitation || $invitation->status !== 'sent' || $invitation->expires_at < now()) {
            return response()->json(['message' => 'Token de convite inválido ou expirado.'], 404);
        }

        // Busca os perfis que foram preparados para este convite
        $rolesToAttach = $invitation->roles()->get();

        // Cria o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'cpf' => $invitation->cpf,
            'password' => Hash::make($request->password),
        ]);
        
        // Associa o usuário aos perfis e contextos definidos no convite
        foreach($rolesToAttach as $role) {
            $user->roles()->attach($role->id);
        }

        // Atualiza o status do convite para finalizado
        $invitation->status = 'completed';
        $invitation->registered_user_id = $user->id;
        $invitation->save();

        return response()->json(['message' => 'Usuário cadastrado com sucesso!'], 201);
    }
}