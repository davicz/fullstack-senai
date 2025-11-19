<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentEvent;
use App\Models\AssessmentEventUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentEventController extends Controller
{
    // -------------------------------------------
    // LISTA DE EVENTOS (para a tela inicial)
    // -------------------------------------------
    public function index(Request $request)
    {
        $user = Auth::user()->load('roles');

        // Apenas admins e, se quiser, docentes
        if (!$user->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $query = AssessmentEvent::query();

        // Tipo: saep / dr / dn
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Busca por nome/código
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Ano (ex: itinerário)
        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // Ordenação simples
        $query->orderBy('start_date', 'desc');

        // Contadores agregados
        $query->withCount([
            'participants as total_students',
            'participants as scheduled_students' => function ($q) {
                $q->whereNotNull('scheduled_date');
            },
            'participants as invited_students' => function ($q) {
                $q->whereNotNull('invite_sent_at');
            },
            'participants as completed_students' => function ($q) {
                $q->whereNotNull('completed_at');
            },
        ]);

        $events = $query->get();

        $formatted = $events->map(function (AssessmentEvent $event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'code' => $event->code,
                'type' => $event->type,
                'description' => $event->description,
                'start_date' => optional($event->start_date)->format('d/m/Y'),
                'end_date' => optional($event->end_date)->format('d/m/Y'),
                'exam_start_date' => optional($event->exam_start_date)->format('d/m/Y'),
                'exam_end_date' => optional($event->exam_end_date)->format('d/m/Y'),
                'status' => $event->computed_status, // planned / open / closed
                'total_students' => $event->total_students,
                'scheduled_students' => $event->scheduled_students,
                'invited_students' => $event->invited_students,
                'completed_students' => $event->completed_students,
            ];
        });

        return response()->json($formatted);
    }

    // -------------------------------------------
    // DETALHES DE UM EVENTO (cards superiores)
    // -------------------------------------------
    public function show(AssessmentEvent $assessmentEvent)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $assessmentEvent->loadCount([
            'participants as total_students',
            'participants as scheduled_students' => function ($q) {
                $q->whereNotNull('scheduled_date');
            },
            'participants as invited_students' => function ($q) {
                $q->whereNotNull('invite_sent_at');
            },
            'participants as completed_students' => function ($q) {
                $q->whereNotNull('completed_at');
            },
        ]);

        return response()->json([
            'id' => $assessmentEvent->id,
            'name' => $assessmentEvent->name,
            'code' => $assessmentEvent->code,
            'type' => $assessmentEvent->type,
            'description' => $assessmentEvent->description,
            'start_date' => optional($assessmentEvent->start_date)->format('d/m/Y'),
            'end_date' => optional($assessmentEvent->end_date)->format('d/m/Y'),
            'exam_start_date' => optional($assessmentEvent->exam_start_date)->format('d/m/Y'),
            'exam_end_date' => optional($assessmentEvent->exam_end_date)->format('d/m/Y'),
            'status' => $assessmentEvent->computed_status,
            'total_students' => $assessmentEvent->total_students,
            'scheduled_students' => $assessmentEvent->scheduled_students,
            'invited_students' => $assessmentEvent->invited_students,
            'completed_students' => $assessmentEvent->completed_students,
        ]);
    }

    // -------------------------------------------
    // LISTA DE ALUNOS DO EVENTO (aba Agendamento)
    // -------------------------------------------
    public function students(Request $request, AssessmentEvent $assessmentEvent)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $query = AssessmentEventUser::with([
            'user.roles',
            'user.regionalDepartment',
            'user.operationalUnit',
            'schoolClass.course',
        ])->where('assessment_event_id', $assessmentEvent->id);

        // Filtros
        if ($request->filled('dr_id')) {
            $query->whereHas('user.regionalDepartment', function ($q) use ($request) {
                $q->where('id', $request->dr_id);
            });
        }

        if ($request->filled('operational_unit_id')) {
            $query->whereHas('user.operationalUnit', function ($q) use ($request) {
                $q->where('id', $request->operational_unit_id);
            });
        }

        if ($request->filled('course_id')) {
            $query->whereHas('schoolClass', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('name')) {
            $name = $request->name;
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        if ($request->filled('cpf')) {
            $cpf = preg_replace('/\D/', '', $request->cpf);
            $query->whereHas('user', function ($q) use ($cpf) {
                $q->where('cpf', 'like', "%{$cpf}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $paginator = $query->paginate($perPage);

        // Contadores agregados para o cabeçalho da aba
        $totals = [
            'total_listed' => $paginator->total(),
            'scheduled' => (clone $query)->whereNotNull('scheduled_date')->count(),
            'invited' => (clone $query)->whereNotNull('invite_sent_at')->count(),
            'completed' => (clone $query)->whereNotNull('completed_at')->count(),
        ];

        $data = $paginator->getCollection()->map(function (AssessmentEventUser $pivot) {
            return [
                'id' => $pivot->id,
                'user_id' => $pivot->user_id,
                'student_name' => $pivot->user->name,
                'student_cpf' => $pivot->user->cpf,
                'school_class_id' => $pivot->school_class_id,
                'school_class_name' => optional($pivot->schoolClass)->name,
                'course_name' => optional($pivot->schoolClass?->course)->name,
                'dr_name' => optional($pivot->user->regionalDepartment)->name,
                'uo_name' => optional($pivot->user->operationalUnit)->name,

                'scheduled_date' => optional($pivot->scheduled_date)->format('d/m/Y'),
                'invite_sent_at' => optional($pivot->invite_sent_at)->format('d/m/Y H:i'),
                'credential_sent_at' => optional($pivot->credential_sent_at)->format('d/m/Y H:i'),
                'credential_code' => $pivot->credential_code,
                'completed_at' => optional($pivot->completed_at)->format('d/m/Y H:i'),

                // Para a timeline no front
                'scheduling_status' => $pivot->scheduling_status,
                'credential_status' => $pivot->credential_status,
                'exam_status' => $pivot->exam_status,

                'status' => $pivot->status,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'totals' => $totals,
        ]);
    }

    // -------------------------------------------
    // ADICIONAR ALUNOS AO EVENTO
    // (botão de ações na parte superior)
    // -------------------------------------------
    public function attachStudents(Request $request, AssessmentEvent $assessmentEvent)
    {
        $user = Auth::user()->load('roles');

        if (!$user->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin')
        )) {
            return response()->json(['message' => 'Apenas administradores podem alterar alunos do evento.'], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'school_class_id' => 'nullable|integer|exists:classes,id',
        ]);

        DB::transaction(function () use ($assessmentEvent, $validated) {
            foreach ($validated['user_ids'] as $userId) {
                AssessmentEventUser::firstOrCreate(
                    [
                        'assessment_event_id' => $assessmentEvent->id,
                        'user_id' => $userId,
                    ],
                    [
                        'school_class_id' => $validated['school_class_id'] ?? null,
                        'status' => 'not_scheduled',
                    ]
                );
            }
        });

        return response()->json(['message' => 'Alunos adicionados ao evento com sucesso.']);
    }

    // -------------------------------------------
    // REMOVER ALUNO DO EVENTO
    // (botão de ações na linha do aluno)
    // -------------------------------------------
    public function detachStudent(AssessmentEvent $assessmentEvent, User $user)
    {
        $authUser = Auth::user()->load('roles');

        if (!$authUser->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin')
        )) {
            return response()->json(['message' => 'Apenas administradores podem alterar alunos do evento.'], 403);
        }

        AssessmentEventUser::where('assessment_event_id', $assessmentEvent->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Aluno removido do evento.']);
    }

    // -------------------------------------------
    // AGENDAR PROVA PARA UM ALUNO
    // (ícone de calendário)
    // -------------------------------------------
    public function scheduleStudent(Request $request, AssessmentEvent $assessmentEvent, User $user)
    {
        $authUser = Auth::user()->load('roles');

        if (!$authUser->roles->contains(fn($role) =>
            str_contains($role->slug, 'admin') || $role->slug === 'teacher'
        )) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validated = $request->validate([
            'scheduled_date' => 'required|date',
        ]);

        $pivot = AssessmentEventUser::firstOrCreate(
            [
                'assessment_event_id' => $assessmentEvent->id,
                'user_id' => $user->id,
            ]
        );

        $pivot->scheduled_date = $validated['scheduled_date'];
        $pivot->status = $pivot->completed_at ? 'completed' : 'scheduled';
        $pivot->save();

        return response()->json(['message' => 'Agendamento registrado com sucesso.']);
    }
}
