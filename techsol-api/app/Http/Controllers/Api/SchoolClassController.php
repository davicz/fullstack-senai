<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    /**
     * Lista as turmas com base nas permissões do usuário.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        // Admin Nacional vê todas as turmas de todas as UOs
        if ($user->roles->contains('slug', 'national_admin')) {
            return SchoolClass::with(['course', 'operationalUnit'])->orderBy('name')->get();
        }

        // Admin Regional vê todas as turmas das UOs do seu DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            $unitIds = $user->regionalDepartment->operationalUnits()->pluck('id');
            return SchoolClass::with(['course', 'operationalUnit'])
                ->whereIn('operational_unit_id', $unitIds)
                ->orderBy('name')->get();
        }

        // Admin de Unidade vê apenas as turmas da sua UO
        if ($user->roles->contains('slug', 'unit_admin')) {
            return SchoolClass::with(['course', 'operationalUnit'])
                ->where('operational_unit_id', $user->operational_unit_id)
                ->orderBy('name')->get();
        }

        // Outros perfis não podem listar todas as turmas por esta rota
        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    /**
     * Cria uma nova turma.
     */
    public function store(Request $request)
    {
        $user = Auth::user()->load('roles');

        // Apenas admins podem criar turmas
        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'codigo' => 'required|string|max:255|unique:classes,codigo',
            'turno' => 'required|in:manha,tarde,noite,integral',
            'origem' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'operational_unit_id' => 'required|exists:operational_units,id',
            'regional_department_id' => 'required|exists:regional_departments,id',
            'docente_responsavel' => 'nullable|string|max:255',
            'quantidade_alunos' => 'nullable|integer|min:0'
        ]);

        // Lógica de permissão para criação
        if ($user->roles->contains('slug', 'regional_admin')) {
            if (!$user->regionalDepartment->operationalUnits()->where('id', $validatedData['operational_unit_id'])->exists()) {
                return response()->json(['message' => 'Permissão para criar turmas apenas em UOs do seu DR.'], 403);
            }
        } else if ($user->roles->contains('slug', 'unit_admin')) {
            if ($user->operational_unit_id != $validatedData['operational_unit_id']) {
                return response()->json(['message' => 'Permissão para criar turmas apenas na sua própria UO.'], 403);
            }
        }

        $schoolClass = SchoolClass::create($validatedData);

        return response()->json($schoolClass, 201);
    }
}