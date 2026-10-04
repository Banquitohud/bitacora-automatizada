<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsCatalogs;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, SeedsCatalogs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalogs();
    }

    public function test_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Depuración Gestor Documental',
            'code' => 'PROJ-1',
            'project_status_id' => $this->projectStatusEjecucion->id,
            'progress' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['code' => 'PROJ-1']);
    }

    public function test_project_progress_must_be_between_0_and_100(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Proyecto inválido',
            'progress' => 125,
        ])->assertSessionHasErrors('progress');

        $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Proyecto inválido 2',
            'progress' => -5,
        ])->assertSessionHasErrors('progress');
    }

    public function test_task_creation_updates_project_progress(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Proyecto con tareas',
            'project_status_id' => $this->projectStatusEjecucion->id,
            'progress' => 0,
            'responsible_id' => $user->id,
        ]);

        // Tarea 1 al 100%
        $this->actingAs($user)->post(route('project-tasks.store', $project), [
            'name' => 'Recolección',
            'progress' => 100,
            'task_status_id' => $this->taskStatusCompletada->id,
        ]);

        // Tarea 2 al 50%
        $this->actingAs($user)->post(route('project-tasks.store', $project), [
            'name' => 'Análisis',
            'progress' => 50,
            'task_status_id' => $this->taskStatusPendiente->id,
        ]);

        $project->refresh();
        $this->assertEquals(75, $project->progress);
    }

    public function test_project_is_overdue_when_estimated_date_passed(): void
    {
        $project = Project::create([
            'name' => 'Proyecto vencido',
            'estimated_end_date' => now()->subDays(5)->toDateString(),
            'project_status_id' => $this->projectStatusEjecucion->id,
        ]);

        $this->assertTrue($project->isOverdue());
    }

    public function test_task_is_overdue_detection(): void
    {
        $project = Project::create(['name' => 'P', 'project_status_id' => $this->projectStatusEjecucion->id]);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'name' => 'Tarea vencida',
            'due_date' => now()->subDays(2)->toDateString(),
            'task_status_id' => $this->taskStatusPendiente->id,
        ]);

        $this->assertTrue($task->isOverdue());
    }

    public function test_admin_can_delete_project_with_soft_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create(['name' => 'P borrable', 'project_status_id' => $this->projectStatusEjecucion->id]);

        $this->actingAs($admin)->delete(route('projects.destroy', $project))->assertRedirect();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_analyst_can_only_update_own_projects(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = Project::create(['name' => 'Propio', 'responsible_id' => $user->id, 'project_status_id' => $this->projectStatusEjecucion->id]);
        $foreign = Project::create(['name' => 'Ajeno', 'responsible_id' => $other->id, 'project_status_id' => $this->projectStatusEjecucion->id]);

        // Puede editar el propio
        $this->actingAs($user)->put(route('projects.update', $own), [
            'name' => 'Propio actualizado',
            'project_status_id' => $this->projectStatusEjecucion->id,
        ])->assertRedirect();

        // No puede editar el ajeno (no es admin)
        $this->actingAs($user)->put(route('projects.update', $foreign), [
            'name' => 'Hackeado',
            'project_status_id' => $this->projectStatusEjecucion->id,
        ])->assertForbidden();
    }
}