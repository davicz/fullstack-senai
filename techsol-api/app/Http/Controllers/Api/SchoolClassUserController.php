<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassUserController extends Controller
{
    protected function canManageClass(SchoolClass $schoolClass, User $user): bool
    {
        if ($user->roles->contains('slug', 'national_admin')) {
            return true;
        }

        if ($user->roles->contains('slug', 'regional_admin')) {
            return $user->regionalDepartment &&
                $user->regionalDepartment->operationalUnits()
                    ->where('id', $schoolClass->operational_unit_id)
                    ->exists();
        }

        if ($user->roles->contains('slug', 'unit_admin')) {
            return (int)$user->operational_unit_id === (int)$schoolClass->operational_unit_id;
        }

        return false;
    }

    /**
     * Associa um ou mais ALUNOS a uma turma específica.
     */
    public function store(Request $request, SchoolClass $schoolClass)
    {
        $authUser = Auth::user()->load('roles');

        if (!$this->canManageClass($schoolClass, $authUser)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($validated['user_ids'] as $studentId) {
            $schoolClass->users()->syncWithoutDetaching([
                $studentId => ['role' => 'student'],
            ]);
        }

        $schoolClass->load('students.roles');

        return response()->json([
            'message'  => 'Alunos associados à turma com sucesso!',
            'students' => $schoolClass->students,
        ]);
    }

    /**
     * Remove um aluno da turma.
     */
    public function removeStudent(SchoolClass $schoolClass, User $user)
    {
        $authUser = Auth::user()->load('roles');

        if (!$this->canManageClass($schoolClass, $authUser)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $schoolClass->users()
            ->wherePivot('role', 'student')
            ->detach($user->id);

        $schoolClass->load('students.roles');

        return response()->json([
            'message'  => 'Aluno removido com sucesso.',
            'students' => $schoolClass->students,
        ]);
    }
}
