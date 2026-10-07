<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyCaseRequest;
use App\Http\Requests\UpdateDailyCaseRequest;
use App\Models\Application;
use App\Models\Audit;
use App\Models\CaseStatus;
use App\Models\DailyCase;
use App\Models\Group;
use App\Models\GroupFamily;
use App\Models\Priority;
use App\Models\Profile;
use App\Models\Project;
use App\Models\RequestType;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyCaseController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DailyCase::class);

        $filters = array_merge([
            'search' => null,
            'status_id' => null,
            'request_type_id' => null,
            'application_id' => null,
            'priority_id' => null,
            'analyst_id' => null,
            'project_id' => null,
            'from' => null,
            'to' => null,
            'flag' => null,
        ], $request->only(['search', 'status_id', 'request_type_id', 'application_id', 'priority_id', 'analyst_id', 'project_id', 'from', 'to', 'flag']));

        $cases = DailyCase::query()
            ->with(['status', 'priority', 'requestType', 'application', 'analyst', 'projects'])
            ->withFilters($filters)
            ->latest('received_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('daily_cases.index', [
            'cases' => $cases,
            'filters' => $filters,
            'statuses' => CaseStatus::active()->ordered()->get(),
            'requestTypes' => RequestType::active()->ordered()->get(),
            'applications' => Application::active()->ordered()->get(),
            'priorities' => Priority::active()->ordered()->get(),
            'analysts' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DailyCase::class);

        return $this->formView(new DailyCase(), 'daily_cases.create');
    }

    public function store(StoreDailyCaseRequest $request): RedirectResponse
    {
        $this->authorize('create', DailyCase::class);

        $case = DB::transaction(function () use ($request) {
            $data = $this->caseData($request);

            $case = DailyCase::create($data);
            $case->projects()->sync($request->input('project_ids', []));

            $this->audit->created($case, 'el caso #'.($case->case_number ?: $case->internal_id));

            return $case;
        });

        return redirect()
            ->route('daily-cases.show', $case)
            ->with('success', 'Caso creado correctamente.');
    }

    public function show(DailyCase $dailyCase): View
    {
        $this->authorize('view', $dailyCase);

        $dailyCase->load([
            'status', 'priority', 'requestType', 'application', 'profile', 'group', 'groupFamily',
            'analyst', 'createdBy', 'updatedBy', 'takenBy', 'closedBy', 'projects',
        ]);

        $history = Audit::query()
            ->where('auditable_type', DailyCase::class)
            ->where('auditable_id', $dailyCase->id)
            ->with('user')
            ->latest()
            ->get();

        return view('daily_cases.show', [
            'case' => $dailyCase,
            'history' => $history,
            'projects' => Project::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDailyCaseRequest $request, DailyCase $dailyCase): RedirectResponse
    {
        $this->authorize('update', $dailyCase);

        $old = $dailyCase->getAttributes();

        DB::transaction(function () use ($request, $dailyCase, $old) {
            $data = $this->caseData($request);

            $statusChanged = isset($data['status_id']) && $data['status_id'] != $old['status_id'];
            $analystChanged = isset($data['analyst_id']) && $data['analyst_id'] != $old['analyst_id'];

            $dailyCase->update($data);
            $dailyCase->projects()->sync($request->input('project_ids', []));

            $this->audit->updated($dailyCase, $old, $dailyCase->getAttributes());

            if ($statusChanged) {
                $this->audit->statusChanged(
                    $dailyCase,
                    'el caso #'.($dailyCase->case_number ?: $dailyCase->id),
                    $old['status_id'],
                    $dailyCase->status_id
                );
            }

            if ($analystChanged) {
                $this->audit->assigned(
                    $dailyCase,
                    'el caso #'.($dailyCase->case_number ?: $dailyCase->id),
                    $dailyCase->analyst
                );
            }

            // Si el nuevo estado es final, registrar cierre automático
            if ($statusChanged && $dailyCase->status?->is_closed && ! $dailyCase->finished_at) {
                $dailyCase->forceFill([
                    'finished_at' => now(),
                    'closed_by' => auth()->id(),
                ])->save();

                $this->audit->closed($dailyCase, 'el caso #'.($dailyCase->case_number ?: $dailyCase->id));
            }
        });

        return redirect()
            ->route('daily-cases.show', $dailyCase)
            ->with('success', 'Caso actualizado correctamente.');
    }

    public function destroy(DailyCase $dailyCase): RedirectResponse
    {
        $this->authorize('delete', $dailyCase);

        $this->audit->deleted($dailyCase, 'el caso #'.($dailyCase->case_number ?: $dailyCase->id));
        $dailyCase->delete();

        return redirect()
            ->route('daily-cases.index')
            ->with('success', 'Caso eliminado (soft delete).');
    }

    /**
     * Acción rápida: el analista toma el caso y lo inicia.
     */
    public function take(DailyCase $dailyCase): RedirectResponse
    {
        $this->authorize('update', $dailyCase);

        $dailyCase->forceFill([
            'analyst_id' => $dailyCase->analyst_id ?? auth()->id(),
            'taken_by' => auth()->id(),
            'started_at' => $dailyCase->started_at ?? now(),
            'updated_by' => auth()->id(),
        ])->save();

        $this->audit->took($dailyCase, 'el caso #'.($dailyCase->case_number ?: $dailyCase->id));

        return back()->with('success', 'Caso tomado en gestión.');
    }

    public function edit(DailyCase $dailyCase): View
    {
        return $this->formView($dailyCase, 'daily_cases.edit');
    }

    /**
     * Acción rápida: cerrar el caso con el estado final configurado.
     */
    public function close(Request $request, DailyCase $dailyCase): RedirectResponse
    {
        $this->authorize('update', $dailyCase);

        $request->validate([
            'result' => ['nullable', 'string'],
        ]);

        $finalStatus = CaseStatus::query()->where('is_closed', true)->orderBy('sort_order')->first();

        DB::transaction(function () use ($request, $dailyCase, $finalStatus) {
            $oldStatus = $dailyCase->status_id;

            $dailyCase->forceFill([
                'status_id' => $finalStatus?->id ?? $dailyCase->status_id,
                'finished_at' => now(),
                'closed_by' => auth()->id(),
                'result' => $request->input('result') ?: $dailyCase->result,
                'updated_by' => auth()->id(),
            ])->save();

            if ($oldStatus != $dailyCase->status_id) {
                $this->audit->statusChanged($dailyCase, 'el caso #'.($dailyCase->case_number ?: $dailyCase->id), $oldStatus, $finalStatus?->name);
            }

            $this->audit->closed($dailyCase, 'el caso #'.($dailyCase->case_number ?: $dailyCase->id));
        });

        return back()->with('success', 'Caso finalizado.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------ */

    private function caseData(Request $request): array
    {
        $defaults = [
            'case_number' => null,
            'received_date' => null,
            'received_time' => null,
            'due_date' => null,
            'requester' => null,
            'affected_user' => null,
            'position' => null,
            'request_type_id' => null,
            'application_id' => null,
            'profile_id' => null,
            'permission' => null,
            'group_id' => null,
            'group_family_id' => null,
            'priority_id' => null,
            'status_id' => null,
            'analyst_id' => null,
            'concept' => null,
            'result' => null,
            'observations' => null,
            'comments' => null,
        ];

        $raw = array_merge($defaults, $request->only(array_keys($defaults)));

        $data = collect($raw)
            ->map(fn ($value) => ($value === '' || $value === null) ? null : $value)
            ->toArray();

        $data['received_time'] = $data['received_time'] ? Carbon::parse($data['received_time'])->format('H:i:s') : null;
        $data['started_at'] = $request->input('started_at') ? Carbon::parse($request->input('started_at')) : null;
        $data['finished_at'] = $request->input('finished_at') ? Carbon::parse($request->input('finished_at')) : null;
        $data['updated_by'] = auth()->id();

        if ($request->isMethod('post')) {
            $data['created_by'] = auth()->id();
            $data['internal_id'] = 'C-'.str_pad((string) ((DailyCase::withTrashed()->max('id') ?? 0) + 2), 6, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    private function formView(DailyCase $case, string $view): View
    {
        return view($view, [
            'case' => $case,
            'statuses' => CaseStatus::active()->ordered()->get(),
            'requestTypes' => RequestType::active()->ordered()->get(),
            'applications' => Application::active()->ordered()->get(),
            'priorities' => Priority::active()->ordered()->get(),
            'profiles' => Profile::ordered()->get(),
            'groups' => Group::with('family')->orderBy('name')->get(),
            'groupFamilies' => GroupFamily::orderBy('name')->get(),
            'analysts' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::query()->orderBy('name')->get(),
        ]);
    }
}