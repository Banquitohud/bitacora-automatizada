<?php

namespace Database\Seeders;

use App\Models\CaseStatus;
use App\Models\DailyCase;
use App\Models\Priority;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectStatus;
use App\Models\RequestType;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id', 'name');
        $statuses = CaseStatus::pluck('id', 'slug');
        $types = RequestType::pluck('id', 'slug');
        $priorities = Priority::pluck('id', 'slug');
        $projectStatuses = ProjectStatus::pluck('id', 'slug');
        $taskStatuses = TaskStatus::pluck('id', 'slug');

        $analystNames = ['Sebastián', 'Usuario 2', 'Usuario 3', 'Usuario 4'];

        // ------- Casos de ejemplo -------
        $tipoCycles = ['creacion', 'modificacion', 'retiro', 'acceso', 'consulta', 'masivo', 'otro'];
        $estadoCycles = ['finalizado', 'finalizado', 'finalizado', 'en-gestion', 'en-analisis', 'pendiente', 'escalado', 'recibido'];
        $prioridadCycles = ['baja', 'media', 'media', 'alta', 'alta', 'critica'];

        for ($i = 1; $i <= 48; $i++) {
            $received = now()->subDays(rand(0, 34))->setTime(rand(8, 17), rand(0, 59));
            $isClosed = $i % 3 === 0;
            $due = $received->copy()->addDays(rand(1, 8));

            DailyCase::firstOrCreate(
                ['case_number' => 'TICKET-'.(1000 + $i)],
                [
                    'internal_id' => 'C-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                    'received_date' => $received->toDateString(),
                    'received_time' => $received->format('H:i:s'),
                    'due_date' => $due->toDateTimeString(),
                    'requester' => 'Solicitante '.($i % 5),
                    'affected_user' => 'usuario.afectado'.($i % 12).'@empresa.com',
                    'position' => ['Analista', 'Supervisor', 'Coordinador'][$i % 3],
                    'request_type_id' => $types[$tipoCycles[$i % count($tipoCycles)]],
                    'application_id' => (($i - 1) % 7) + 1,
                    'profile_id' => (($i - 1) % 4) + 1,
                    'permission' => 'PERM_'.($i % 6).(($i % 2) ? '' : '_X'),
                    'group_id' => (($i - 1) % 6) + 1,
                    'group_family_id' => (($i - 1) % 3) + 1,
                    'priority_id' => $priorities[$prioridadCycles[$i % count($prioridadCycles)]],
                    'status_id' => $statuses[$estadoCycles[$i % count($estadoCycles)]],
                    'analyst_id' => $users[$analystNames[$i % 4]],
                    'started_at' => $received->copy()->addHours(2)->toDateTimeString(),
                    'finished_at' => $isClosed ? $received->copy()->addHours(rand(5, 70))->toDateTimeString() : null,
                    'concept' => "Análisis GSI del caso {$i}: se valida la solicitud, los perfiles involucrados y los permisos requeridos.",
                    'result' => $isClosed ? 'Solicitud gestionada y aplicada correctamente.' : null,
                    'observations' => $i % 7 === 0 ? 'Requiere validación adicional del dueño de la aplicación.' : null,
                    'created_by' => $users['Sebastián'],
                    'updated_by' => $users['Sebastián'],
                    'taken_by' => $i % 3 === 0 ? null : $users[$analystNames[$i % 4]],
                    'closed_by' => $isClosed ? $users[$analystNames[$i % 4]] : null,
                ]
            );
        }

        // ------- Proyectos de ejemplo -------
        $projectDefs = [
            ['name' => 'Depuración Gestor Documental', 'code' => 'PROJ-GD-2026', 'status' => 'en-ejecucion', 'responsable' => 'Sebastián'],
            ['name' => 'Normalización de accesos SAP', 'code' => 'PROJ-SAP-2026', 'status' => 'en-ejecucion', 'responsable' => 'Usuario 2'],
            ['name' => 'Migración de perfiles a Active Directory', 'code' => 'PROJ-AD-2026', 'status' => 'en-revision', 'responsable' => 'Usuario 3'],
            ['name' => 'Revisión de permisos por familias de grupos', 'code' => 'PROJ-FAM-2026', 'status' => 'planeado', 'responsable' => 'Usuario 4'],
            ['name' => 'Carga inicial de usuarios al CRM', 'code' => 'PROJ-CRM-2025', 'status' => 'finalizado', 'responsable' => 'Sebastián'],
        ];

        foreach ($projectDefs as $def) {
            $project = Project::firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'description' => 'Proyecto de ejemplo del área GSI.',
                    'start_date' => now()->subDays(20)->toDateString(),
                    'estimated_end_date' => now()->addDays(rand(5, 30))->toDateString(),
                    'actual_end_date' => $def['status'] === 'finalizado' ? now()->subDays(2)->toDateString() : null,
                    'responsible_id' => $users[$def['responsable']],
                    'project_status_id' => $projectStatuses[$def['status']],
                    'priority_id' => $priorities['media'],
                    'progress' => 0,
                    'observations' => null,
                    'created_by' => $users['Sebastián'],
                    'updated_by' => $users['Sebastián'],
                ]
            );

            $taskNames = ['Recolección de información', 'Validación', 'Análisis', 'Ejecución', 'Validación final', 'Cierre'];
            foreach ($taskNames as $ti => $tname) {
                $done = $ti <= 2 || $def['status'] === 'finalizado';
                ProjectTask::firstOrCreate(
                    ['project_id' => $project->id, 'name' => $tname],
                    [
                        'description' => null,
                        'responsible_id' => $users[$analystNames[$ti % 4]],
                        'task_status_id' => $done ? $taskStatuses['completada'] : (rand(0, 1) ? $taskStatuses['en-progreso'] : $taskStatuses['pendiente']),
                        'priority_id' => $priorities['media'],
                        'start_date' => now()->subDays(18)->toDateString(),
                        'due_date' => now()->addDays(rand(3, 20))->toDateString(),
                        'completed_date' => $done ? now()->subDays(rand(1, 10))->toDateString() : null,
                        'progress' => $done ? 100 : rand(10, 60),
                        'created_by' => $users['Sebastián'],
                        'updated_by' => $users['Sebastián'],
                    ]
                );
            }
        }

        // Relacionar algunos casos con proyectos
        $projects = Project::all();
        $cases = DailyCase::limit(10)->get();
        foreach ($cases as $index => $case) {
            $case->projects()->syncWithoutDetaching([$projects[$index % $projects->count()]->id]);
        }

        // Recalcular avances de proyecto a partir de tareas
        foreach ($projects as $project) {
            $project->syncProgress();
        }
    }
}