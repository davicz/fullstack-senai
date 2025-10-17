<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Por enquanto, vamos permitir que qualquer usuário autenticado veja a lista de cursos.
        return Course::orderBy('name')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Apenas usuários com um perfil de 'admin' podem criar cursos.
        // A função contains() com um callback verifica se qualquer um dos perfis do usuário contém 'admin' no slug.
        if (!Auth::user()->roles->contains(fn($role) => str_contains($role->slug, 'admin'))) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|unique:courses|max:255',
        ]);

        $course = Course::create($validatedData);

        return response()->json($course, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        // A ser implementado no futuro, se necessário.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        // A ser implementado no futuro, se necessário.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        // A ser implementado no futuro, se necessário.
    }
}