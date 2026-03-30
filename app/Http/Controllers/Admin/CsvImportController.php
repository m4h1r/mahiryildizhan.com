<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CsvImportController extends Controller
{
    public function __construct(private readonly CsvImportService $csvImportService)
    {
    }

    public function index(): View
    {
        return view('admin.import', [
            'title' => 'CSV Import',
            'heading' => 'CSV Import',
            'tables' => CsvImportService::supportedTables(),
            'report' => session('import_report'),
        ]);
    }

    public function import(Request $request, ?string $table = null): RedirectResponse
    {
        $validated = $request->validate([
            'table' => ['nullable', 'string'],
            'dry_run' => ['nullable', 'boolean'],
            'import_all' => ['nullable', 'boolean'],
        ]);

        $importAll = (bool) ($validated['import_all'] ?? false);
        $dryRun = (bool) ($validated['dry_run'] ?? false);
        $targetTable = $table ?? ($validated['table'] ?? null);

        if (! $importAll && $targetTable === null) {
            return back()->withErrors(['table' => 'Select a table or enable import all.'])->withInput();
        }

        $report = $importAll
            ? $this->csvImportService->importAll($dryRun)
            : $this->csvImportService->import($targetTable, $dryRun);

        return to_route('admin.import.index')->with('import_report', $report);
    }
}
