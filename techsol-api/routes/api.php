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

// Rota de Login (Pública)
Route::post('/login', [AuthController::class, 'login']);

// Rota de Registro (Pública, mas validada pelo token no controller)
Route::post('/register', [AuthController::class, 'register']);

// Agrupando rotas que precisam de autenticação
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/login/profile', [AuthController::class, 'selectProfile']);

    // Rota para convidar colaboradores
    Route::post('/invites/start', [InviteController::class, 'start']);
    Route::post('/invites/{invitation}/roles', [InviteController::class, 'assignRoles']);
    Route::post('/invites/{invitation}/context', [InviteController::class, 'assignContext']);
    Route::post('/invites/{invitation}/send', [InviteController::class, 'send']);

    // Rota para LISTAR os convites
    Route::get('/invites', [InviteController::class, 'index']);

    // Rota de exemplo para obter dados do usuário logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rota para listar e pesquisar colaboradores
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/export', [UserController::class, 'export']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    Route::apiResource('regional-departments', RegionalDepartmentController::class);
    Route::apiResource('operational-units', OperationalUnitController::class);

    Route::apiResource('courses', CourseController::class);
    Route::apiResource('classes', SchoolClassController::class);

    // NOVAS ROTAS PARA GERIR MATRÍCULAS
    Route::post('/classes/{schoolClass}/users', [SchoolClassUserController::class, 'store']);
    // Futuramente, podemos adicionar a rota de remoção:
    // Route::delete('/classes/{schoolClass}/users/{user}', [SchoolClassUserController::class, 'destroy']);

    Route::apiResource('evaluations', EvaluationController::class);
    Route::post('/evaluations/{evaluation}/questions', [QuestionController::class, 'store']);
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store']);
    Route::put('/answers/{answer}', [AnswerController::class, 'update']);
    Route::get('/evaluations/{evaluation}/answers', [AnswerController::class, 'index']);

    Route::get('/users/{user}/classes', [UserClassAssociationController::class, 'index']);
    Route::put('/users/{user}/classes', [UserClassAssociationController::class, 'sync']);

    Route::get('/turnos', fn() => [
        ['id'=>'manha','name'=>'Manhã'],
        ['id'=>'tarde','name'=>'Tarde'],
        ['id'=>'noite','name'=>'Noite'],
        ['id'=>'integral','name'=>'Integral'],
    ]);

    Route::get('/origens', fn() => [
        ['id'=>1,'name'=>'SIAC'],
        ['id'=>2,'name'=>'IMPORTADO'],
        ['id'=>3,'name'=>'OUTRO SISTEMA'],
    ]);

    Route::post('/classes/{schoolClass}/teachers', [SchoolClassController::class, 'storeTeacher']);
    Route::post('/classes/{schoolClass}/students', [SchoolClassUserController::class, 'store']);

});