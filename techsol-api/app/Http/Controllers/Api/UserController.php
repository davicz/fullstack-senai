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

class UserController extends Controller
{
    /**
     * Exibe uma lista de usuários com base nas permissões do usuário logado.
     */
    public function index(Request $request)
    {
        $loggedInUser = Auth::user()->load('roles');
        $query = User::query();

        // Lógica de permissão hierárquica (já funcional)
        if ($loggedInUser->roles->contains('slug', 'national_admin')) {
            // Vê tudo
        } else if ($loggedInUser->roles->contains('slug', 'regional_admin')) {
            $query->where('regional_department_id', $loggedInUser->regional_department_id);
        } else if ($loggedInUser->roles->contains('slug', 'unit_admin')) {
            $query->where('operational_unit_id', $loggedInUser->operational_unit_id);
        } else {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        // --- NOVOS FILTROS AVANÇADOS ---
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'LIKE', "%{$searchTerm}%")->orWhere('email', 'LIKE', "%{$searchTerm}%")->orWhere('cpf', 'LIKE', "%{$searchTerm}%"));
        }
        if ($request->has('regional_department_id')) {
            $query->where('regional_department_id', $request->input('regional_department_id'));
        }
        if ($request->has('operational_unit_id')) {
            $query->where('operational_unit_id', $request->input('operational_unit_id'));
        }
        if ($request->has('role_id')) {
            $query->whereHas('roles', fn($q) => $q->where('role_id', $request->input('role_id')));
        }
        // O filtro de Status (ativo/inativo) pode ser adicionado aqui no futuro se a tabela 'users' tiver essa coluna.

        $users = $query->with('roles')->orderBy('name', 'asc')->paginate(15);

        return response()->json($users);
    }
    
    // ... O resto dos métodos (show, update, export) que já estão corretos ...
    public function show(User $user)
    {
        return response()->json($user);
    }

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