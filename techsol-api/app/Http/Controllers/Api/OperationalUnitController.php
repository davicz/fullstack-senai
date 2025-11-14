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

    // Admin Nacional vê todas as UOs
    if ($user->roles->contains('slug', 'national_admin')) {
        return OperationalUnit::orderBy('name')->get();
    }

    // Admin Regional vê apenas as UOs do seu DR
    if ($user->roles->contains('slug', 'regional_admin')) {

        // Primeiro tenta o campo direto do user
        $drId = $user->regional_department_id ?? null;

        // Se não tiver, tenta buscar no pivot do role (compatibilidade)
        if (!$drId) {
            $regionalAdminRole = $user->roles->where('slug', 'regional_admin')->first();
            $drId = $regionalAdminRole?->pivot?->regional_department_id ?? null;
        }

        if ($drId) {
            return OperationalUnit::where('regional_department_id', $drId)
                ->orderBy('name')
                ->get();
        }

        // se não conseguimos determinar DR para esse regional, retornamos 403
        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    // Método para criar uma nova Unidade Operacional
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Apenas Admin Nacional e Regional podem criar UOs
        if (!$user->roles->contains('slug', 'national_admin') && !$user->roles->contains('slug', 'regional_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'regional_department_id' => 'required|exists:regional_departments,id',
        ]);

        // Se quem cria é um Admin Regional, força a criação dentro do seu próprio DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            $regionalAdminRole = $user->roles->where('slug', 'regional_admin')->first();
            $drId = $regionalAdminRole->pivot->regional_department_id;

            if ($validatedData['regional_department_id'] != $drId) {
                return response()->json(['message' => 'Permissão para criar UO apenas no seu próprio Departamento Regional.'], 403);
            }
        }

        $unit = OperationalUnit::create($validatedData);

        return response()->json($unit, 201);
    }
}