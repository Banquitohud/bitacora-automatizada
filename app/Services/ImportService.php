<?php

namespace App\Services;

use App\Models\Application;
use App\Models\CaseStatus;
use App\Models\DailyCase;
use App\Models\Group;
use App\Models\GroupFamily;
use App\Models\Priority;
use App\Models\Profile;
use App\Models\RequestType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    /** Columnas esperadas: clave normalizada => campo del modelo */
    public const COLUMN_MAP = [
        'numero_caso' => 'case_number',
        'numero_de_caso' => 'case_number',
        'numero_de_caso_ticket' => 'case_number',
        'numero_caso_ticket' => 'case_number',
        'ticket' => 'case_number',
        'caso' => 'case_number',
        'fecha_recepcion' => 'received_date',
        'fecha_de_recepcion' => 'received_date',
        'hora_recepcion' => 'received_time',
        'fecha_limite' => 'due_date',
        'fecha_limite_2' => 'due_date',
        'solicitante' => 'requester',
        'usuario_afectado' => 'affected_user',
        'usuario' => 'affected_user',
        'cargo' => 'position',
        'tipo_solicitud' => 'request_type',
        'tipo_de_solicitud' => 'request_type',
        'aplicacion' => 'application',
        'aplicacion_2' => 'application',
        'perfil_solicitado' => 'profile',
        'perfil' => 'profile',
        'permiso' => 'permission',
        'permiso_transaccion' => 'permission',
        'grupo' => 'group',
        'familia' => 'group_family',
        'grupo_familia' => 'group_family',
        'prioridad' => 'priority',
        'estado' => 'status',
        'analista' => 'analyst',
        'concepto_gsi' => 'concept',
        'concepto' => 'concept',
        'resultado' => 'result',
        'observaciones' => 'observations',
    ];

    public const REQUIRED = ['case_number', 'received_date'];

    /**
     * Lee el archivo y normaliza las filas en una colección de arreglos.
     */
    public function readFile($file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! is_file($path)) {
            return ['headers' => [], 'rows' => collect()];
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        // startRow auto-detecta, nullValue '' para celdas vacías, sin fórmulas, formatData true
        $rows = $sheet->toArray(null, '', false, true);

        if (empty($rows)) {
            return ['headers' => [], 'rows' => collect()];
        }

        $headers = array_map([$this, 'normalizeKey'], (array) array_shift($rows));

        $data = collect($rows)
            ->filter()
            ->map(fn ($row) => array_combine($headers, array_pad(array_values((array) $row), count($headers), '')))
            ->values();

        $spreadsheet->disconnectWorksheets();

        return ['headers' => $headers, 'rows' => $data];
    }

    /**
     * Prepara la vista previa: valida cada fila y devuelve metadatos para confirmación.
     */
    public function preview($file): array
    {
        $parsed = $this->readFile($file);
        $headers = $parsed['headers'];

        $mapped = $this->mapRowToFields($parsed['rows'], $headers);

        $existing = DailyCase::query()
            ->whereIn('case_number', $mapped->pluck('case_number')->filter())
            ->pluck('case_number');

        $previewRows = $mapped->map(function ($fields, $index) use ($existing) {
            $errors = $this->validateRow($fields, $index + 1, $existing);

            return [
                'row_number' => $index + 1,
                'fields' => $fields,
                'errors' => $errors,
                'is_valid' => empty($errors) && ! empty($fields['case_number'])
                    && ! $existing->contains($fields['case_number']),
                'is_duplicate' => ! empty($fields['case_number']) && $existing->contains($fields['case_number']),
            ];
        });

        return [
            'headers' => $headers,
            'rows' => $previewRows,
            'valid_count' => $previewRows->where('is_valid')->count(),
            'error_count' => $previewRows->where(fn ($r) => ! $r['is_valid'])->count(),
            'expected_headers' => $this->expectedHeaders(),
        ];
    }

    public function expectedHeaders(): array
    {
        return [
            'Número de caso', 'Fecha de recepción', 'Hora de recepción', 'Fecha límite',
            'Solicitante', 'Usuario afectado', 'Cargo', 'Tipo de solicitud', 'Aplicación',
            'Perfil', 'Permiso', 'Grupo', 'Familia', 'Prioridad', 'Estado', 'Analista',
            'Concepto GSI', 'Resultado', 'Observaciones',
        ];
    }

    /**
     * Importa las filas válidas. Devuelve el conteo de importados.
     */
    public function import($file): array
    {
        $parsed = $this->readFile($file);
        $mapped = $this->mapRowToFields($parsed['rows'], $parsed['headers']);

        $imported = 0;
        $skippedDuplicates = 0;
        $errors = [];

        foreach ($mapped as $index => $raw) {
            if ($raw['case_number'] && DailyCase::query()->where('case_number', $raw['case_number'])->exists()) {
                $skippedDuplicates++;

                continue;
            }

            $errorsInRow = $this->validateRow($raw, $index + 1, collect());

            if (! empty($errorsInRow)) {
                $errors[] = 'Fila '.($index + 1).': '.implode('; ', $errorsInRow);

                continue;
            }

            $data = $this->fieldsToAttributes($raw);

            $case = DailyCase::create($data);

            app(AuditService::class)->imported($case, 'flujo diario', 1);

            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped_duplicates' => $skippedDuplicates,
            'errors' => $errors,
        ];
    }

    /* ------------------------------------------------------------------
     | Internos
     | ------------------------------------------------------------------ */

    protected function mapRowToFields(Collection $rows, array $headers): Collection
    {
        return $rows->map(function ($row) use ($headers) {
            $fields = [];

            foreach (self::COLUMN_MAP as $headerKey => $field) {
                $index = array_search($headerKey, $headers);
                if ($index !== false) {
                    $value = trim((string) ($row[$index] ?? ''));
                    if ($value !== '' && ! isset($fields[$field])) {
                        $fields[$field] = $value;
                    }
                }
            }

            return $fields;
        });
    }

    protected function validateRow(array $fields, int $rowNumber, $existing): array
    {
        $errors = [];

        if (empty($fields['case_number'])) {
            $errors[] = 'Falta el número de caso';
        } elseif ($existing->contains($fields['case_number'])) {
            $errors[] = 'Número de caso duplicado';
        }

        if (empty($fields['received_date'])) {
            $errors[] = 'Falta la fecha de recepción';
        } else {
            try {
                Carbon::parse($fields['received_date']);
            } catch (\Exception) {
                $errors[] = 'Fecha de recepción inválida';
            }
        }

        return $errors;
    }

    protected function fieldsToAttributes(array $fields): array
    {
        $data = [
            'case_number' => $fields['case_number'] ?? null,
            'received_date' => $this->parseDate($fields['received_date'] ?? null),
            'received_time' => $this->parseTime($fields['received_time'] ?? null),
            'due_date' => $this->parseDate($fields['due_date'] ?? null),
            'requester' => $fields['requester'] ?? null,
            'affected_user' => $fields['affected_user'] ?? null,
            'position' => $fields['position'] ?? null,
            'permission' => $fields['permission'] ?? null,
            'concept' => $fields['concept'] ?? null,
            'result' => $fields['result'] ?? null,
            'observations' => $fields['observations'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'internal_id' => 'C-'.str_pad((string) ((DailyCase::withTrashed()->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT),
        ];

        $data['request_type_id'] = $this->resolveRelation(RequestType::class, $fields['request_type'] ?? null);
        $data['application_id'] = $this->resolveRelation(Application::class, $fields['application'] ?? null);
        $data['profile_id'] = $this->resolveRelation(Profile::class, $fields['profile'] ?? null);
        $data['group_family_id'] = $this->resolveRelation(GroupFamily::class, $fields['group_family'] ?? null);
        $data['group_id'] = $this->resolveRelation(Group::class, $fields['group'] ?? null);
        $data['priority_id'] = $this->resolveRelation(Priority::class, $fields['priority'] ?? null);
        $data['status_id'] = $this->resolveRelation(CaseStatus::class, $fields['status'] ?? null);
        $data['analyst_id'] = $this->resolveUser($fields['analyst'] ?? null);

        return $data;
    }

    protected function normalizeKey(string $key): string
    {
        $value = trim($key);

        // Quitar tildes para normalizar los encabezados (Número -> numero)
        $value = str_replace($value, ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'U', 'N']);

        $value = str($value)->slug();
        $value = str_replace($value, '-', '_');

        return $value;
    }

    protected function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }

    protected function parseTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception) {
            return null;
        }
    }

    protected function resolveRelation(string $model, ?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $instance = $model::query()
            ->where('name', $name)
            ->orWhere('slug', \Illuminate\Support\Str::slug($name))
            ->first();

        return $instance?->id;
    }

    protected function resolveUser(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $user = User::query()
            ->where('name', 'like', "%{$name}%")
            ->orWhere('email', $name)
            ->first();

        return $user?->id;
    }
}
