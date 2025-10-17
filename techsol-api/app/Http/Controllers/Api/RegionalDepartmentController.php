<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegionalDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegionalDepartmentController extends Controller
{
    // Método para listar todos os Departamentos Regionais
    public function index()
    {
        // NOVA VERIFICAÇÃO DE PERMISSÃO
        if (!Auth::user()->roles->contains('slug', 'national_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        return RegionalDepartment::orderBy('name')->get();
    }

    // Método para criar um novo Departamento Regional
    public function store(Request $request)
    {
        // NOVA VERIFICAÇÃO DE PERMISSÃO
        if (!Auth::user()->roles->contains('slug', 'national_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|unique:regional_departments|max:255',
        ]);

        $department = RegionalDepartment::create($validatedData);

        return response()->json($department, 201);
    }
}