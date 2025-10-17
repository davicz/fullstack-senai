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
        $loggedInUser = Auth::user();
        $query = User::query();

        // Admin Nacional vê tudo.
        if ($loggedInUser->roles->contains('slug', 'national_admin')) {
            // Nenhuma restrição é aplicada.
        } 
        // Admin Regional vê todos os usuários das UOs do seu DR.
        else if ($loggedInUser->roles->contains('slug', 'regional_admin')) {
            
            // --- LÓGICA CORRIGIDA FINAL ---
            // A "casa" do admin está na própria tabela users
            $drId = $loggedInUser->regional_department_id;
            
            // Busca todos os usuários que pertencem ao mesmo DR
            $query->where('regional_department_id', $drId);

        }
        // Admin de Unidade vê todos os usuários da sua UO.
        else if ($loggedInUser->roles->contains('slug', 'unit_admin')) {
            // A "casa" do admin está na própria tabela users
            $uoId = $loggedInUser->operational_unit_id;
            $query->where('operational_unit_id', $uoId);
        }
        else {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('cpf', 'LIKE', "%{$searchTerm}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->paginate(15);

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