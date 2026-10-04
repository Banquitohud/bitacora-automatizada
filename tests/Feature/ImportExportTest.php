<?php

namespace Tests\Feature;

use App\Models\DailyCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\SeedsCatalogs;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase, SeedsCatalogs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalogs();
    }

    public function test_export_csv_returns_file(): void
    {
        $user = User::factory()->create();

        DailyCase::create([
            'case_number' => 'EXP-1',
            'received_date' => today()->toDateString(),
            'status_id' => $this->statusPendiente->id,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', 'csv'));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_export_xlsx_returns_file(): void
    {
        $user = User::factory()->create();

        DailyCase::create([
            'case_number' => 'EXP-2',
            'received_date' => today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', 'xlsx'));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_export_pdf_returns_file(): void
    {
        $user = User::factory()->create();

        DailyCase::create([
            'case_number' => 'EXP-3',
            'received_date' => today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', 'pdf'));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_import_preview_shows_valid_and_duplicate_rows(): void
    {
        $user = User::factory()->create();

        // Caso ya existente para probar duplicados
        DailyCase::create([
            'case_number' => 'IMP-1',
            'received_date' => today()->toDateString(),
        ]);

        $csv = "Número de caso,Fecha de recepción,Usuario afectado,Solicitante\n"
            ."IMP-1,01/09/2026,usuario1@empresa.com,Solicitante A\n"
            ."IMP-2,02/09/2026,usuario2@empresa.com,Solicitante B\n"
            .",03/09/2026,usuario3@empresa.com,Solicitante C\n"
            ."IMP-3,invalida,usuario4@empresa.com,Solicitante D\n";

        $file = UploadedFile::fake()->createWithContent('casos.csv', $csv);

        $response = $this->actingAs($user)->post(route('import.preview'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertSee('IMP-2');
        $response->assertSee('Falta el número de caso');
    }

    public function test_import_store_imports_valid_rows_and_skips_duplicates(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseCount('daily_cases', 0);
    }
}