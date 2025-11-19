<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\UserController; 
use App\Http\Controllers\Api\RegionalDepartmentController; 
use App\Http\Controllers\Api\OperationalUnitController; 
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\SchoolClassUserController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\UserClassAssociationController;
use App\Http\Controllers\Api\CompetencyController;

// Rota de Login (Pública)
Route::post('/login', [AuthController::class, 'login']);

// Rota de Registro (Pública, mas validada pelo token no controller)
Route::post('/register', [AuthController::class, 'register']);


// Agrupando rotas que precisam de autenticação
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/login/profile', [AuthController::class, 'selectProfile']);

    // Convites
    Route::post('/invites/start', [InviteController::class, 'start']);
    Route::post('/invites/{invitation}/roles', [InviteController::class, 'assignRoles']);
    Route::post('/invites/{invitation}/context', [InviteController::class, 'assignContext']);
    Route::post('/invites/{invitation}/send', [InviteController::class, 'send']);
    Route::get('/invites', [InviteController::class, 'index']);

    // Usuário logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Colaboradores
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/export', [UserController::class, 'export']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    // Estrutura organizacional
    Route::apiResource('regional-departments', RegionalDepartmentController::class);
    Route::apiResource('operational-units', OperationalUnitController::class);
    Route::get('regional-departments', [RegionalDepartmentController::class, 'index']);


    // Cursos e turmas
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('classes', SchoolClassController::class);

    // Matrículas em turmas (ALUNOS)
    Route::post('/classes/{schoolClass}/students', [SchoolClassUserController::class, 'store']);
    Route::delete('/classes/{schoolClass}/students/{user}', [SchoolClassUserController::class, 'removeStudent']);

    // Docentes da turma
    Route::post('/classes/{schoolClass}/teachers', [SchoolClassController::class, 'storeTeacher']);
    Route::delete('/classes/{schoolClass}/teachers/{user}', [SchoolClassController::class, 'removeTeacher']);

    // Avaliações
    Route::apiResource('evaluations', EvaluationController::class);
    Route::post('/evaluations/{evaluation}/questions', [QuestionController::class, 'store']);
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store']);
    Route::put('/answers/{answer}', [AnswerController::class, 'update']);
    Route::get('/evaluations/{evaluation}/answers', [AnswerController::class, 'index']);

    // Turmas por usuário (associações)
    Route::get('/users/{user}/classes', [UserClassAssociationController::class, 'index']);
    Route::put('/users/{user}/classes', [UserClassAssociationController::class, 'sync']);

    // Listas auxiliares
    Route::get('/turnos', fn() => [
        ['id' => 'manha',    'name' => 'Manhã'],
        ['id' => 'tarde',    'name' => 'Tarde'],
        ['id' => 'noite',    'name' => 'Noite'],
        ['id' => 'integral', 'name' => 'Integral'],
    ]);

    Route::get('/origens', fn() => [
        ['id' => 1, 'name' => 'SIAC'],
        ['id' => 2, 'name' => 'IMPORTADO'],
        ['id' => 3, 'name' => 'OUTRO SISTEMA'],
    ]);

    // CRUD de Competências (apenas admins podem criar/editar/deletar)
    Route::apiResource('competencies', CompetencyController::class);

    // Vincular competência a curso
    Route::post('/competencies/{competency}/courses', [CompetencyController::class, 'attachToCourse']);

    // Progresso do aluno em competências
    Route::get('/users/{user?}/competencies/progress', [CompetencyController::class, 'getStudentProgress']);

    // Ranking por competência
    Route::get('/competencies/{competency}/ranking', [CompetencyController::class, 'getCompetencyRanking']);
});
