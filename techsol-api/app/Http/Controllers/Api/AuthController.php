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

        $user = Auth::user()->load('roles');

        if ($user->roles->isEmpty()) {
            return response()->json(['message' => 'Este usuário não possui perfis de acesso configurados.'], 403);
        }
        
        // Se o usuário tiver apenas um perfil, retornamos token final e o selected_role
        if ($user->roles->count() === 1) {
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login bem-sucedido!',
                'access_token' => $token,
                'user' => $user,
                'selected_role' => $user->roles->first(), // mantém retorno consistente
            ]);
        }
        
        // Múltiplos perfis => gerar token temporário e enviar user + temporary_token
        $temporaryToken = $user->createToken('temporary-token')->plainTextToken;

        return response()->json([
            'message' => 'Múltiplos perfis encontrados. Por favor, selecione um.',
            'temporary_token' => $temporaryToken,
            'user' => $user,
        ]);
    }

    /**
     * ETAPA 2: Gera o token final baseado no perfil escolhido.
     *
     * OBS: O frontend deve salvar o `selected_role` retornado neste JSON (localStorage)
     * e a aplicação frontend enviará o header X-Selected-Role em requisições subsequentes.
     */
    public function selectProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_role_id' => 'required|integer',
        ]);

        // Procura se o usuário possui o role_id pedido
        // Note: se sua tabela pivô tiver id você pode alterar a lógica; aqui buscamos por role_id
        $selectedRole = $user->roles()->where('role_id', $validated['user_role_id'])->first();

        if (!$selectedRole) {
            return response()->json(['message' => 'Perfil inválido ou não pertence a este usuário.'], 403);
        }
        
        // Remove tokens temporários
        $user->tokens()->where('name', 'temporary-token')->delete();

        // Gera token final
        $finalToken = $user->createToken('auth-token')->plainTextToken;

        // Retornamos o selected_role junto com o token e o usuário.
        // O frontend deve salvar selected_role em localStorage (siac_user.selected_role)
        return response()->json([
            'message' => 'Perfil selecionado com sucesso!',
            'access_token' => $finalToken,
            'user' => $user->fresh()->load('roles'),
            'selected_role' => $selectedRole,
        ]);
    }
    
    /**
     * O MÉTODO DE REGISTRO QUE JÁ ESTAVA CORRETO
     */
    public function register(Request $request)
{
    $request->validate([
        'token' => 'required|string',
        'name' => 'required|string|max:100',
        'password' => 'required|string|min:8', // Adicione suas regras de regex se quiser
        // Campos opcionais
        'address' => 'nullable|string',
        'phone' => 'nullable|string',
        'city' => 'nullable|string',
        'uf' => 'nullable|string|size:2',
        'cep' => 'nullable|string',
        'education_level' => 'nullable|string',
    ]);

    $invitation = Invitation::where('token', $request->token)->first();

    if (!$invitation || $invitation->status !== 'sent' || $invitation->expires_at < now()) {
        return response()->json(['message' => 'Convite inválido ou expirado.'], 404);
    }

    // --- MUDANÇA AQUI: Verifica se já existe antes de criar ---
    $user = User::where('email', $invitation->email)
                ->orWhere('cpf', $invitation->cpf)
                ->first();

    $userData = [
        'name' => $request->name,
        'password' => Hash::make($request->password),
        'address' => $request->address,
        'phone' => $request->phone,
        'city' => $request->city,
        'uf' => $request->uf,
        'cep' => $request->cep,
        'education_level' => $request->education_level,
        'interest_area' => $request->interest_area,
        'interest_course' => $request->interest_course,
        // Se o usuário já existe, não mudamos e-mail nem cpf para evitar conflitos
    ];

    if ($user) {
        // SE JÁ EXISTE: Atualiza os dados
        $user->update($userData);
    } else {
        // SE NÃO EXISTE: Cria do zero com Email e CPF do convite
        $userData['email'] = $invitation->email;
        $userData['cpf'] = $invitation->cpf;
        $user = User::create($userData);
    }

    // Associa os perfis e contextos
    $rolesToAttach = $invitation->roles()->get();
    
    foreach($rolesToAttach as $role) {
        // syncWithoutDetaching evita duplicar se o usuário já tiver esse papel
        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'regional_department_id' => $role->pivot->regional_department_id,
                'operational_unit_id' => $role->pivot->operational_unit_id
            ]
        ]);
        
        // Atualiza contexto principal se ainda não tiver
        if(!$user->regional_department_id && $role->pivot->regional_department_id) {
            $user->regional_department_id = $role->pivot->regional_department_id;
        }
        if(!$user->operational_unit_id && $role->pivot->operational_unit_id) {
            $user->operational_unit_id = $role->pivot->operational_unit_id;
        }
    }
    $user->save(); 

    $invitation->status = 'completed';
    $invitation->registered_user_id = $user->id;
    $invitation->save();

    return response()->json(['message' => 'Cadastro realizado com sucesso!'], 201);
}
}
