<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvImportRequest;
use App\Import\ImportProcessor;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public const ROUTE_IMPORT = 'import';

    private ImportProcessor $importProcessor;

    public function __construct(ImportProcessor $importProcessor)
    {
        $this->importProcessor = $importProcessor;
    }

    public function import(CsvImportRequest $request)
    {
        DB::transaction(fn () => $this->processImport($request));
    }

    private function processImport(CsvImportRequest $request)
    {
        $path = $request->file(CsvImportRequest::FILE)->getRealPath();
        Excel::import(
            $this->importProcessor,
            $path,
        );
    }
}
