<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * Exibe uma lista de usuários com base nas permissões do usuário logado.
     */
    public function index(Request $request)
    {
        $loggedInUser = Auth::user()->load('roles');
        $query = User::query();

        // --- Tratamento do filtro role_id (se enviado) ---
        $roleId = $request->input('role_id', null);
        if ($roleId !== null && $roleId !== '') {
            // valida se o role existe
            $role = Role::find($roleId);
            if ($role) {
                $query->whereHas('roles', function (Builder $q) use ($roleId) {
                    $q->where('id', $roleId);
                });
            } else {
                // Se passou um role_id inválido, retornamos vazio (status 200 com paginação vazia)
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'message' => 'role_id inválido'
                    ]
                ]);
            }
        }

        // --- Agora aplicamos as regras de visibilidade conforme o papel do usuário logado ---
        // Admin Nacional vê tudo (não precisa restringir)
        if ($loggedInUser->roles->contains('slug', 'national_admin')) {
            // nada a fazer — vê tudo (mas ainda respeita o filtro role_id se existia)
        }
        // Admin Regional: vê apenas usuários da sua DR, e não vê usuários com perfis acima (nacional)
        else if ($loggedInUser->roles->contains('slug', 'regional_admin')) {
            $query->where('regional_department_id', $loggedInUser->regional_department_id);

            // Bloquear visualização de usuários com perfis que não deveriam aparecer:
            // - Não mostrar Administradores Nacionais
            $query->whereDoesntHave('roles', function (Builder $q) {
                $q->where('slug', 'national_admin');
            });

            // Observação: se você quiser também ocultar outros Regionais (para que um regional não veja outros regionais),
            // descomente a linha abaixo:
            // $query->whereDoesntHave('roles', fn($q) => $q->where('slug', 'regional_admin'));
        }
        // Admin de Unidade: vê apenas usuários da sua UO, e não vê Admins Nacionais/Regionais
        else if ($loggedInUser->roles->contains('slug', 'unit_admin')) {
            $query->where('operational_unit_id', $loggedInUser->operational_unit_id);

            $query->whereDoesntHave('roles', function (Builder $q) {
                $q->whereIn('slug', ['national_admin', 'regional_admin']);
            });
        }
        // Default: sem permissão para ver listagens gerais
        else {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        // --- Filtros de pesquisa --- 
        if ($request->has('search') && trim($request->input('search')) !== '') {
            $searchTerm = trim($request->input('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('cpf', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtros extras do PDF (DR, Escola)
        if ($request->has('regional_department_id') && $request->input('regional_department_id') !== '') {
            $query->where('regional_department_id', $request->input('regional_department_id'));
        }
        if ($request->has('operational_unit_id') && $request->input('operational_unit_id') !== '') {
            $query->where('operational_unit_id', $request->input('operational_unit_id'));
        }

        $users = $query->with('roles')->orderBy('name', 'asc')->paginate(15);

        return response()->json($users);
    }

    /**
     * Mostra um usuário (show). Torna a resposta mais amigável caso não exista.
     */
    public function show($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            return response()->json($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
    }

    /**
     * Atualiza usuário.
     */
    public function update(Request $request, User $user)
    {
        $loggedInUser = Auth::user();
        $loggedInUser->load('roles');

        if ($loggedInUser->id === $user->id) {
            return response()->json(['message' => 'Não é possível alterar seu próprio perfil por esta rota.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'cpf' => ['sometimes', 'string', 'size:11', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'roles' => 'sometimes|array',
        ]);

        if (isset($validatedData['roles'])) {
            // Esperamos que sejam IDs simples [1,2,3]
            $requestedRoleIds = $validatedData['roles'];
            $requestedRoles = Role::whereIn('id', $requestedRoleIds)->get();

            if ($loggedInUser->roles->contains('slug', 'regional_admin')) {
                if ($requestedRoles->contains('slug', 'national_admin')) {
                    return response()->json(['message' => 'Permissão negada. Administradores Regionais não podem criar Administradores Nacionais.'], 403);
                }
            }

            if ($loggedInUser->roles->contains('slug', 'unit_admin')) {
                if ($requestedRoles->contains('slug', 'national_admin') || $requestedRoles->contains('slug', 'regional_admin')) {
                    return response()->json(['message' => 'Permissão negada para atribuir este nível de perfil.'], 403);
                }
            }
        }

        $user->fill($request->only(['name', 'cpf']));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if (isset($validatedData['roles'])) {
            // sincroniza por IDs simples
            $user->roles()->sync($validatedData['roles']);
        }

        $user->save();

        return response()->json([
            'message' => 'Usuário atualizado com sucesso!',
            'user' => $user->fresh()->load('roles'),
        ]);
    }

    public function export(Request $request)
    {
        return response()->json(['message' => 'Funcionalidade de exportação em desenvolvimento.']);
    }
}
