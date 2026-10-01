<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CaseStatus;
use App\Models\Group;
use App\Models\GroupFamily;
use App\Models\Priority;
use App\Models\Profile;
use App\Models\ProjectStatus;
use App\Models\RequestType;
use App\Models\SlaConfiguration;
use App\Models\TaskStatus;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Catálogos administrables: clave => [modelo, campos editables].
     */
    private const CATALOGS = [
        'case_statuses' => ['model' => CaseStatus::class, 'fields' => ['name', 'color', 'sort_order', 'is_active', 'is_initial', 'is_closed']],
        'request_types' => ['model' => RequestType::class, 'fields' => ['name', 'sort_order', 'is_active']],
        'priorities' => ['model' => Priority::class, 'fields' => ['name', 'color', 'sort_order', 'is_active']],
        'applications' => ['model' => Application::class, 'fields' => ['name', 'code', 'description', 'sort_order', 'is_active']],
        'profiles' => ['model' => Profile::class, 'fields' => ['name', 'description']],
        'groups' => ['model' => Group::class, 'fields' => ['name', 'group_family_id', 'description']],
        'group_families' => ['model' => GroupFamily::class, 'fields' => ['name', 'description']],
        'project_statuses' => ['model' => ProjectStatus::class, 'fields' => ['name', 'color', 'sort_order', 'is_active']],
        'task_statuses' => ['model' => TaskStatus::class, 'fields' => ['name', 'color', 'sort_order', 'is_active']],
        'sla' => ['model' => SlaConfiguration::class, 'fields' => ['name', 'unit', 'value', 'applies_to', 'request_type_id', 'priority_id', 'is_active']],
    ];

    public function __construct(private readonly AuditService $audit)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $active = $request->input('tab', 'case_statuses');

        return view('settings.index', [
            'activeTab' => $active,
            'catalogs' => $this->catalogData(),
            'groupFamilies' => GroupFamily::orderBy('name')->get(),
            'requestTypes' => RequestType::active()->ordered()->get(),
            'priorities' => Priority::active()->ordered()->get(),
        ]);
    }

    public function store(Request $request, string $catalog): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $config = self::CATALOGS[$catalog] ?? abort(404);
        $model = $config['model'];
        $data = $this->validatedData($request, $config['fields']);

        $record = $model::create($data);

        $this->audit->created($record, 'el registro "'.($record->name ?? $record->id).'"');

        return redirect()
            ->route('settings.index', ['tab' => $catalog])
            ->with('success', 'Registro agregado correctamente.');
    }

    public function update(Request $request, string $catalog, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $config = self::CATALOGS[$catalog] ?? abort(404);
        $model = $config['model'];
        $record = $model::findOrFail($id);

        $old = $record->getAttributes();
        $data = $this->validatedData($request, $config['fields']);

        $record->update($data);

        $this->audit->updated($record, $old, $record->getAttributes());

        return redirect()
            ->route('settings.index', ['tab' => $catalog])
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(string $catalog, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $config = self::CATALOGS[$catalog] ?? abort(404);
        $model = $config['model'];
        $record = $model::findOrFail($id);

        $this->audit->deleted($record, 'el registro "'.($record->name ?? $record->id).'"');
        $record->delete();

        return redirect()
            ->route('settings.index', ['tab' => $catalog])
            ->with('success', 'Registro eliminado (soft delete).');
    }

    /* ------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------ */

    private function validatedData(Request $request, array $fields): array
    {
        $data = $request->only($fields);

        if (array_key_exists('name', $data) && ! array_key_exists('slug', $data)) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = $request->boolean('is_active');
        }
        if (array_key_exists('is_initial', $data)) {
            $data['is_initial'] = $request->boolean('is_initial');
        }
        if (array_key_exists('is_closed', $data)) {
            $data['is_closed'] = $request->boolean('is_closed');
        }

        return $data;
    }

    private function catalogData(): array
    {
        return [
            'case_statuses' => CaseStatus::withTrashed()->ordered()->get(),
            'request_types' => RequestType::withTrashed()->ordered()->get(),
            'priorities' => Priority::withTrashed()->ordered()->get(),
            'applications' => Application::withTrashed()->ordered()->get(),
            'profiles' => Profile::withTrashed()->orderBy('name')->get(),
            'groups' => Group::withTrashed()->with('family')->orderBy('name')->get(),
            'group_families' => GroupFamily::withTrashed()->orderBy('name')->get(),
            'project_statuses' => ProjectStatus::withTrashed()->ordered()->get(),
            'task_statuses' => TaskStatus::withTrashed()->ordered()->get(),
            'sla' => SlaConfiguration::withTrashed()->orderBy('name')->get(),
        ];
    }
}


