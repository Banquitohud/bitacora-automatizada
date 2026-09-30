<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCaseRequest;
use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(private readonly ImportService $import)
    {
    }

    public function index(): View
    {
        return view('import.index', [
            'expectedHeaders' => $this->import->expectedHeaders(),
        ]);
    }

    public function preview(ImportCaseRequest $request): View
    {
        $preview = $this->import->preview($request->file('file'));

        // Guardamos el archivo temporalmente para la confirmación
        $path = $request->file('file')->store('imports', 'local');

        return view('import.preview', [
            'preview' => $preview,
            'temporaryPath' => $path,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'temporary_path' => ['required', 'string'],
        ]);

        $file = \Illuminate\Support\Facades\Storage::disk('local')->path($request->input('temporary_path'));

        if (! is_file($file)) {
            return back()->withErrors(['file' => 'El archivo temporal ya no existe. Vuelve a intentarlo.']);
        }

        $result = $this->import->import($file);

        \Illuminate\Support\Facades\Storage::disk('local')->delete($request->input('temporary_path'));

        $message = "Importación completada: {$result['imported']} registros importados, "
            ."{$result['skipped_duplicates']} duplicados omitidos.";

        if (! empty($result['errors'])) {
            $message .= ' Errores: '.implode(' | ', array_slice($result['errors'], 0, 5));
        }

        return redirect()
            ->route('import.index')
            ->with('success', $message);
    }
}