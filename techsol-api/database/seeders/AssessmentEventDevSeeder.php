<?php

namespace Database\Seeders;

use App\Models\AssessmentEvent;
use App\Models\AssessmentEventUser;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AssessmentEventDevSeeder extends Seeder
{
    public function run(): void
    {
        // Busca alunos e turmas existentes
        $students = User::whereHas('roles', fn($q) => $q->where('slug', 'student'))->get();
        $classes = SchoolClass::all();

        if ($students->isEmpty() || $classes->isEmpty()) {
            $this->command?->warn('Nenhum aluno ou turma encontrada para popular os eventos.');
            return;
        }

        // Cria alguns eventos SAEP
        $events = [
            [
                'name' => 'SAEP 2024-1',
                'code' => 'SAEP_2024_1',
                'type' => 'saep',
                'start_date' => '2024-03-01',
                'end_date' => '2024-06-30',
                'exam_start_date' => '2024-04-15',
                'exam_end_date' => '2024-06-15',
            ],
            [
                'name' => 'SAEP 2024-2',
                'code' => 'SAEP_2024_2',
                'type' => 'saep',
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-30',
                'exam_start_date' => '2024-10-01',
                'exam_end_date' => '2024-12-10',
            ],
        ];

        foreach ($events as $data) {
            $event = AssessmentEvent::firstOrCreate(
                ['code' => $data['code']],
                $data
            );

            // Para cada evento, vincula uma parte dos alunos
            $sample = $students->random(min($students->count(), 40)); // até 40 alunos por evento

            foreach ($sample as $student) {
                $class = $classes->random();

                $pivot = AssessmentEventUser::firstOrCreate(
                    [
                        'assessment_event_id' => $event->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'school_class_id' => $class->id,
                        'status' => 'not_scheduled',
                    ]
                );

                // Gera um "estado" aleatório para a timeline
                $rand = rand(1, 4);
                $examStart = Carbon::parse($event->exam_start_date);
                $examEnd = Carbon::parse($event->exam_end_date);

                if ($rand >= 2) {
                    // agendado
                    $pivot->scheduled_date = $examStart->copy()->addDays(rand(0, $examEnd->diffInDays($examStart)));
                    $pivot->status = 'scheduled';
                }

                if ($rand >= 3) {
                    // convite/credencial
                    $pivot->invite_sent_at = $event->start_date
                        ? Carbon::parse($event->start_date)->addDays(rand(0, 10))
                        : now()->subDays(rand(5, 15));
                    $pivot->credential_code = strtoupper(bin2hex(random_bytes(4)));
                    $pivot->credential_sent_at = $pivot->invite_sent_at->copy()->addHours(rand(1, 48));
                    $pivot->status = 'invited';
                }

                if ($rand === 4) {
                    // prova realizada
                    $pivot->completed_at = $pivot->scheduled_date
                        ? Carbon::parse($pivot->scheduled_date)->addHours(rand(1, 3))
                        : now()->subDays(rand(1, 10));
                    $pivot->status = 'completed';
                }

                $pivot->save();
            }
        }

        $this->command?->info('Eventos SAEP de desenvolvimento criados com alunos vinculados.');
    }
}
