<?php

namespace Tests\Feature;

use App\Models\DailyCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsCatalogs;
use Tests\TestCase;

class DailyCaseTest extends TestCase
{
    use RefreshDatabase, SeedsCatalogs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalogs();
    }

    private function analyst(): User
    {
        return User::factory()->create();
    }

    public function test_analyst_can_create_a_case(): void
    {
        $user = $this->analyst();

        $response = $this->actingAs($user)->post(route('daily-cases.store'), [
            'case_number' => 'TCK-100',
            'received_date' => now()->toDateString(),
            'received_time' => '09:30',
            'request_type_id' => $this->requestType->id,
            'application_id' => $this->application->id,
            'priority_id' => $this->priorityMedia->id,
            'affected_user' => 'usuario.test@empresa.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('daily_cases', ['case_number' => 'TCK-100']);

        $case = DailyCase::where('case_number', 'TCK-100')->first();
        $this->assertNotNull($case->internal_id);
        $this->assertNotNull($case->created_by);

        $this->assertDatabaseHas('audits', [
            'auditable_type' => DailyCase::class,
            'auditable_id' => $case->id,
            'action' => 'created',
        ]);
    }

    public function test_duplicate_case_number_is_rejected(): void
    {
        $user = $this->analyst();

        DailyCase::create([
            'case_number' => 'TCK-999',
            'received_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('daily-cases.store'), [
            'case_number' => 'TCK-999',
            'received_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('case_number');
        $this->assertDatabaseCount('daily_cases', 1);
    }

    public function test_status_change_is_audited(): void
    {
        $user = $this->analyst();
        $case = DailyCase::create([
            'case_number' => 'TCK-200',
            'received_date' => now()->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('daily-cases.update', $case), [
            'case_number' => 'TCK-200',
            'received_date' => now()->toDateString(),
            'status_id' => $this->statusEnAnalisis->id,
            'analyst_id' => $user->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('audits', [
            'auditable_id' => $case->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_closing_a_case_sets_finished_at(): void
    {
        $user = $this->analyst();
        $case = DailyCase::create([
            'case_number' => 'TCK-300',
            'received_date' => now()->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('daily-cases.close', $case), [
            'result' => 'Gestionado y aplicado.',
        ]);

        $case->refresh();
        $this->assertNotNull($case->finished_at);
        $this->assertEquals($this->statusFinalizado->id, $case->status_id);
        $this->assertEquals($user->id, $case->closed_by);

        $this->assertDatabaseHas('audits', [
            'auditable_id' => $case->id,
            'action' => 'closed',
        ]);
    }

    public function test_admin_can_delete_a_case_using_soft_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $case = DailyCase::create([
            'case_number' => 'TCK-401',
            'received_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->delete(route('daily-cases.destroy', $case))
            ->assertRedirect();

        $this->assertSoftDeleted('daily_cases', ['id' => $case->id]);
    }

    public function test_analyst_cannot_delete_a_case(): void
    {
        $user = $this->analyst();
        $case = DailyCase::create([
            'case_number' => 'TCK-400',
            'received_date' => now()->toDateString(),
            'analyst_id' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('daily-cases.destroy', $case))
            ->assertForbidden();

        $this->assertDatabaseHas('daily_cases', ['id' => $case->id]);
    }

    public function test_time_calculations_are_correct(): void
    {
        $received = now()->subDays(3)->setTime(8, 0);
        $case = DailyCase::create([
            'case_number' => 'TCK-500',
            'received_date' => $received->toDateString(),
            'received_time' => '08:00:00',
            'started_at' => $received->copy()->addHours(3),
            'finished_at' => $received->copy()->addHours(9),
            'status_id' => $this->statusFinalizado->id,
        ]);

        $this->assertEquals(3.0, $case->timeToStartHours());
        $this->assertEquals(6.0, $case->handlingTimeHours());
        $this->assertEquals(9.0, $case->totalTimeHours());
    }

    public function test_overdue_scope_detects_vencidos(): void
    {
        DailyCase::create([
            'case_number' => 'TCK-601',
            'received_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDays(2)->toDateTimeString(),
            'status_id' => $this->statusPendiente->id,
        ]);

        $this->assertEquals(1, DailyCase::overdue()->count());
    }

    public function test_semaforo_flag_returns_red_for_overdue_case(): void
    {
        $case = DailyCase::create([
            'case_number' => 'TCK-700',
            'received_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDay()->toDateTimeString(),
            'status_id' => $this->statusPendiente->id,
        ]);

        $this->assertEquals('red', $case->flag());
    }
}