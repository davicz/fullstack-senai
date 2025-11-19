<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    /**
     * Monta a query base com todos os relacionamentos necessários.
     */
    protected function queryWithRelations()
    {
        return SchoolClass::with([
            'course',
            'operationalUnit.regionalDepartment',
            'teachers.roles',
            'students.roles',
        ])->orderBy('name');
    }

    /**
     * Lista as turmas com base nas permissões do usuário.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        $query = $this->queryWithRelations();

        // Admin Nacional vê todas as turmas de todas as UOs
        if ($user->roles->contains('slug', 'national_admin')) {
            return $query->get();
        }

        // Admin Regional vê todas as turmas das UOs do seu DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            $unitIds = $user->regionalDepartment
                ? $user->regionalDepartment->operationalUnits()->pluck('id')
                : [];

            return $query
                ->whereIn('operational_unit_id', $unitIds)
                ->get();
        }

        // Admin de Unidade vê apenas as turmas da sua UO
        if ($user->roles->contains('slug', 'unit_admin')) {
            return $query
                ->where('operational_unit_id', $user->operational_unit_id)
                ->get();
        }

        // Docente vê apenas turmas onde leciona
        if ($user->roles->contains('slug', 'teacher')) {
            return $query
                ->whereHas('teachers', fn($q) => $q->where('users.id', $user->id))
                ->get();
        }

        // Aluno vê apenas turmas onde está matriculado
        if ($user->roles->contains('slug', 'student')) {
            return $query
                ->whereHas('students', fn($q) => $q->where('users.id', $user->id))
                ->get();
        }

        return response()->json(['message' => 'Acesso não autorizado.'], 403);
    }

    /**
     * Verifica se o usuário pode gerenciar (CRUD) uma turma.
     */
    protected function canManageClass(SchoolClass $schoolClass, User $user): bool
    {
        // Admin nacional pode tudo
        if ($user->roles->contains('slug', 'national_admin')) {
            return true;
        }

        // Admin regional: só UOs do seu DR
        if ($user->roles->contains('slug', 'regional_admin')) {
            return $user->regionalDepartment &&
                $user->regionalDepartment->operationalUnits()
                    ->where('id', $schoolClass->operational_unit_id)
                    ->exists();
        }

        // Admin de unidade: só na sua UO
        if ($user->roles->contains('slug', 'unit_admin')) {
            return (int) $user->operational_unit_id === (int) $schoolClass->operational_unit_id;
        }

        return false;
    }

    /**
     * Verifica se o usuário pode visualizar uma turma.
     */
    protected function canViewClass(SchoolClass $schoolClass, User $user): bool
    {
        // Se pode gerenciar, pode ver
        if ($this->canManageClass($schoolClass, $user)) {
            return true;
        }

        // Docente da turma
        if ($user->roles->contains('slug', 'teacher')) {
            return $schoolClass->teachers()
                ->where('users.id', $user->id)
                ->exists();
        }

        // Aluno da turma
        if ($user->roles->contains('slug', 'student')) {
            return $schoolClass->students()
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Cria uma nova turma.
     */
    public function store(Request $request)
    {
        $user = Auth::user()->load('roles');

        // Apenas perfis admin podem criar turmas
        if (!$user->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'codigo'                 => 'nullable|string|max:255|unique:classes,codigo',
            'turno'                  => 'required|in:manha,tarde,noite,integral',
            'origem'                 => 'nullable|string|max:255',
            'course_id'              => 'required|exists:courses,id',
            'operational_unit_id'    => 'required|exists:operational_units,id',
            'regional_department_id' => 'nullable|exists:regional_departments,id',
        ]);

        // Deduz DR pela UO, se não vier
        if (empty($validated['regional_department_id'])) {
            $uo = \App\Models\OperationalUnit::find($validated['operational_unit_id']);
            if ($uo) {
                $validated['regional_department_id'] = $uo->regional_department_id;
            }
        }

        // Checa escopo (usa um model "temporário")
        $tmp = new SchoolClass($validated);
        if (!$this->canManageClass($tmp, $user)) {
            return response()->json([
                'message' => 'Permissão negada para criar turma neste contexto.',
            ], 403);
        }

        // Se não informar código, usa o próprio nome
        if (empty($validated['codigo'])) {
            $validated['codigo'] = $validated['name'];
        }

        $schoolClass = SchoolClass::create($validated);

        // Carrega com relacionamentos
        $schoolClass = $this->queryWithRelations()->find($schoolClass->id);

        return response()->json($schoolClass, 201);
    }

    /**
     * Mostra detalhes de uma turma.
     */
    public function show($id)
    {
        $user = Auth::user()->load('roles');

        // Busca SEM usar model binding, pra evitar qualquer zica
        $schoolClass = $this->queryWithRelations()->findOrFail($id);

        if (!$this->canViewClass($schoolClass, $user)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        return response()->json($schoolClass);
    }

    /**
     * Atualiza uma turma.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user()->load('roles');

        // Primeiro busca a turma "crua"
        $schoolClass = SchoolClass::findOrFail($id);

        if (!$this->canManageClass($schoolClass, $user)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'name'                   => 'sometimes|required|string|max:255',
            'codigo'                 => 'nullable|string|max:255|unique:classes,codigo,' . $schoolClass->id,
            'turno'                  => 'sometimes|required|in:manha,tarde,noite,integral',
            'origem'                 => 'nullable|string|max:255',
            'course_id'              => 'sometimes|required|exists:courses,id',
            'operational_unit_id'    => 'sometimes|required|exists:operational_units,id',
            'regional_department_id' => 'nullable|exists:regional_departments,id',
        ]);

        // Se mudou a UO e não veio DR, deduz
        if (isset($validated['operational_unit_id']) && empty($validated['regional_department_id'])) {
            $uo = \App\Models\OperationalUnit::find($validated['operational_unit_id']);
            if ($uo) {
                $validated['regional_department_id'] = $uo->regional_department_id;
            }
        }

        // Se mandou 'codigo' vazio mas com 'name', usa o name
        if (array_key_exists('codigo', $validated)
            && empty($validated['codigo'])
            && !empty($validated['name'] ?? null)
        ) {
            $validated['codigo'] = $validated['name'];
        }

        // Atualiza e salva
        $schoolClass->fill($validated);
        $schoolClass->save();

        // Recarrega com todos os relacionamentos bonitinhos
        $schoolClass = $this->queryWithRelations()->findOrFail($schoolClass->id);

        return response()->json($schoolClass);
    }

    /**
     * Remove uma turma.
     */
    public function destroy($id)
    {
        $user = Auth::user()->load('roles');
        $schoolClass = SchoolClass::findOrFail($id);

        if (!$this->canManageClass($schoolClass, $user)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $schoolClass->delete();

        return response()->json(['message' => 'Turma excluída com sucesso.']);
    }

    /**
     * Adiciona um ou mais professores à turma.
     */
    public function storeTeacher(Request $request, SchoolClass $schoolClass)
    {
        $authUser = Auth::user()->load('roles');

        if (!$this->canManageClass($schoolClass, $authUser)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'teacher_ids'   => 'required|array',
            'teacher_ids.*' => 'exists:users,id',
        ]);

        foreach ($validated['teacher_ids'] as $teacherId) {
            $schoolClass->users()->syncWithoutDetaching([
                $teacherId => ['role' => 'teacher'],
            ]);
        }

        $schoolClass->load('teachers.roles');

        return response()->json([
            'message'  => 'Professores adicionados com sucesso!',
            'teachers' => $schoolClass->teachers,
        ]);
    }

    /**
     * Remove um professor da turma.
     */
    public function removeTeacher(SchoolClass $schoolClass, User $user)
    {
        $authUser = Auth::user()->load('roles');

        if (!$this->canManageClass($schoolClass, $authUser)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $schoolClass->users()
            ->wherePivot('role', 'teacher')
            ->detach($user->id);

        $schoolClass->load('teachers.roles');

        return response()->json([
            'message'  => 'Professor removido com sucesso.',
            'teachers' => $schoolClass->teachers,
        ]);
    }
}
