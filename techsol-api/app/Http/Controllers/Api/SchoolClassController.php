<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    /**
     * Listagem com hierarquia completa.
     */
    public function index()
    {
        $user = Auth::user()->load([
            'roles',
            'regionalDepartment',
            'regionalDepartment.operationalUnits'
        ]);

        // NATIONAL ADMIN → vê tudo
        if ($user->roles->contains('slug', 'national_admin')) {
            return SchoolClass::with([
                'course',
                'operationalUnit',
                'operationalUnit.regionalDepartment',
                'teachers',
                'students'
            ])->orderBy('name')->get();
        }

        // REGIONAL ADMIN → vê UOs do seu DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            $unitIds = $user->regionalDepartment->operationalUnits->pluck('id');

            return SchoolClass::with([
                'course',
                'operationalUnit',
                'operationalUnit.regionalDepartment',
                'teachers',
                'students'
            ])
                ->whereIn('operational_unit_id', $unitIds)
                ->orderBy('name')
                ->get();
        }

        // UNIT ADMIN → vê somente da própria UO
        if ($user->roles->contains('slug', 'unit_admin')) {
            return SchoolClass::with([
                'course',
                'operationalUnit',
                'operationalUnit.regionalDepartment',
                'teachers',
                'students'
            ])
                ->where('operational_unit_id', $user->operational_unit_id)
                ->orderBy('name')
                ->get();
        }

        // TEACHER → vê apenas suas próprias turmas
        if ($user->roles->contains('slug', 'teacher')) {
            return $user->classes()
                ->with([
                    'course',
                    'operationalUnit',
                    'operationalUnit.regionalDepartment',
                    'teachers',
                    'students'
                ])
                ->orderBy('name')
                ->get();
        }

        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    /**
     * Criar turma.
     */
    public function store(Request $request)
    {
        $user = Auth::user()->load(['roles', 'regionalDepartment']);

        if (!$user->roles->contains(fn($r) => str_contains($r->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'codigo' => 'required|string|max:255|unique:classes,codigo',
            'turno' => 'required|in:manha,tarde,noite,integral',
            'origem' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'operational_unit_id' => 'required|exists:operational_units,id',
            'regional_department_id' => 'required|exists:regional_departments,id',
            'docente_responsavel' => 'nullable|string|max:255',
            'quantidade_alunos' => 'nullable|integer|min:0',

            // Professores enviados junto com a criação
            'docente_ids' => 'array',
            'docente_ids.*' => 'exists:users,id'
        ]);

        // Validação hierárquica
        if ($user->roles->contains('slug', 'regional_admin')) {
            if (!$user->regionalDepartment->operationalUnits()
                ->where('id', $validated['operational_unit_id'])
                ->exists()
            ) {
                return response()->json([
                    'message' => 'Permissão para criar turmas apenas em UOs do seu DR.'
                ], 403);
            }
        }

        if ($user->roles->contains('slug', 'unit_admin')) {
            if ($validated['operational_unit_id'] != $user->operational_unit_id) {
                return response()->json([
                    'message' => 'Permissão para criar turmas apenas na sua própria UO.'
                ], 403);
            }
        }

        $schoolClass = SchoolClass::create($validated);

        // Associar professores caso enviados
        if ($request->has('docente_ids')) {
            $schoolClass->users()->syncWithoutDetaching(
                collect($request->docente_ids)
                    ->mapWithKeys(fn($id) => [$id => ['role' => 'teacher']])
                    ->toArray()
            );
        }

        return $schoolClass->load('teachers', 'students');
    }

    /**
     * Atualizar turma.
     */
    public function update(Request $request, SchoolClass $schoolClass)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($r) => str_contains($r->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'codigo' => 'sometimes|string|max:255|unique:classes,codigo,' . $schoolClass->id,
            'turno' => 'sometimes|in:manha,tarde,noite,integral',
            'origem' => 'nullable|string|max:255',
            'course_id' => 'sometimes|exists:courses,id',
            'operational_unit_id' => 'sometimes|exists:operational_units,id',
            'regional_department_id' => 'sometimes|exists:regional_departments,id',
            'docente_responsavel' => 'nullable|string|max:255',
            'quantidade_alunos' => 'nullable|integer|min:0',

            'docente_ids' => 'array',
            'docente_ids.*' => 'exists:users,id'
        ]);

        $schoolClass->update($validated);

        // Atualizar professores
        if ($request->has('docente_ids')) {
            $schoolClass->users()->syncWithoutDetaching(
                collect($request->docente_ids)
                    ->mapWithKeys(fn($id) => [$id => ['role' => 'teacher']])
                    ->toArray()
            );
        }

        return $schoolClass->load('teachers', 'students');
    }

    /**
     * Remover turma.
     */
    public function destroy(SchoolClass $schoolClass)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($r) => str_contains($r->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $schoolClass->users()->detach();  
        $schoolClass->delete();

        return response()->json(['message' => 'Turma removida com sucesso.']);
    }

    /**
     * Associar professores.
     */
    public function storeTeacher(Request $request, SchoolClass $schoolClass)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($r) => str_contains($r->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['teacher_ids'] as $id) {
            $schoolClass->users()->syncWithoutDetaching([
                $id => ['role' => 'teacher']
            ]);
        }

        return response()->json([
            'message' => 'Professores adicionados com sucesso!',
            'teachers' => $schoolClass->teachers()->get()
        ]);
    }
}
