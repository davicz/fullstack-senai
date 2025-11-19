<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperationalUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationalUnitController extends Controller
{
    // Método para listar Unidades Operacionais
    public function index()
    {
        $user = Auth::user();

        // Verifica se o usuário existe (caso a rota tenha escapado do middleware)
        if (!$user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        // Admin Nacional vê todas as UOs (com dados do DR)
        if ($user->roles->contains('slug', 'national_admin')) {
            return OperationalUnit::with('regionalDepartment')->orderBy('name')->get();
        }

        // Admin Regional vê apenas as UOs do seu DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            // Primeiro tenta o campo direto do user
            $drId = $user->regional_department_id ?? null;

            // Se não tiver, tenta buscar no pivot do role
            if (!$drId) {
                $regionalAdminRole = $user->roles->where('slug', 'regional_admin')->first();
                $drId = $regionalAdminRole?->pivot?->regional_department_id ?? null;
            }

            if ($drId) {
                return OperationalUnit::with('regionalDepartment')
                    ->where('regional_department_id', $drId)
                    ->orderBy('name')
                    ->get();
            }

            return response()->json(['message' => 'Acesso não autorizado (DR não definido).'], 403);
        }

        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    // Método para criar uma nova Unidade Operacional
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }
        
        // Apenas Admin Nacional e Regional podem criar UOs
        if (!$user->roles->contains('slug', 'national_admin') && !$user->roles->contains('slug', 'regional_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'regional_department_id' => 'required|exists:regional_departments,id',
            'code' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'days_submission' => 'nullable|integer',
            'approval_percentage' => 'nullable|numeric',
        ]);

        // Se quem cria é um Admin Regional, força a criação dentro do seu próprio DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            $regionalAdminRole = $user->roles->where('slug', 'regional_admin')->first();
            $drId = $regionalAdminRole->pivot->regional_department_id ?? $user->regional_department_id;

            if ($validatedData['regional_department_id'] != $drId) {
                return response()->json(['message' => 'Permissão para criar UO apenas no seu próprio Departamento Regional.'], 403);
            }
        }

        $unit = OperationalUnit::create($validatedData);

        return response()->json($unit, 201);
    }
}