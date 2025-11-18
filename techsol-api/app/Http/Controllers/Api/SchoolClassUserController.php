<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassUserController extends Controller
{
    /**
     * Associa ALUNOS a uma turma.
     * (Apenas role = student)
     */
    public function store(Request $request, SchoolClass $schoolClass)
    {
        $user = Auth::user()->load('roles');

        // Permissões
        if ($user->roles->contains('slug', 'regional_admin')) {
            if (!$user->regionalDepartment
                ->operationalUnits()
                ->where('id', $schoolClass->operational_unit_id)
                ->exists()
            ) {
                return response()->json([
                    'message' => 'Permissão para gerir matrículas apenas em UOs do seu DR.'
                ], 403);
            }
        } else if ($user->roles->contains('slug', 'unit_admin')) {
            if ($user->operational_unit_id != $schoolClass->operational_unit_id) {
                return response()->json([
                    'message' => 'Permissão para gerir matrículas apenas na sua própria UO.'
                ], 403);
            }
        } else if (!$user->roles->contains('slug', 'national_admin')) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        // Validação
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['student_ids'] as $id) {
            $schoolClass->users()->syncWithoutDetaching([
                $id => ['role' => 'student']
            ]);
        }

        return response()->json([
            'message' => 'Alunos adicionados com sucesso!',
            'students' => $schoolClass->students()->get()
        ]);
    }
}
