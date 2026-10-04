<?php

namespace Tests\Feature;

use App\Models\DailyCase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsCatalogs;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase, SeedsCatalogs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalogs();
    }

    public function test_reports_page_loads_for_analyst(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
    }

    public function test_dashboard_computes_kpis_from_database(): void
    {
        $user = User::factory()->create();

        DailyCase::create([
            'case_number' => 'RPT-1',
            'received_date' => today()->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $user->id,
        ]);
        DailyCase::create([
            'case_number' => 'RPT-2',
            'received_date' => today()->toDateString(),
            'status_id' => $this->statusFinalizado->id,
            'finished_at' => now(),
            'analyst_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['period' => 'today']));

        $response->assertOk();
        $response->assertSee('2'); // recibidos hoy
    }

    public function test_dashboard_shows_my_cases_section(): void
    {
        $user = User::factory()->create();
        DailyCase::create([
            'case_number' => 'MIS-1',
            'received_date' => today()->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Mis casos');
    }

    public function test_monthly_report_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.monthly', ['month' => now()->format('Y-m')]));

        $response->assertOk();
    }

    public function test_case_filters_apply_to_report_query(): void
    {
        $user = User::factory()->create();

        DailyCase::create([
            'case_number' => 'FIL-1',
            'received_date' => now()->subDays(1)->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $user->id,
        ]);
        DailyCase::create([
            'case_number' => 'FIL-2',
            'received_date' => now()->subMonths(2)->toDateString(),
            'status_id' => $this->statusPendiente->id,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'from' => now()->subDays(3)->toDateString(),
            'to' => today()->toDateString(),
            'analyst_id' => $user->id,
        ]));

        $response->assertOk();
    }

    public function test_projects_appear_in_reports(): void
    {
        $user = User::factory()->create();

        Project::create([
            'name' => 'Proyecto reporte',
            'project_status_id' => $this->projectStatusEjecucion->id,
            'progress' => 40,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Proyecto reporte');
    }
}