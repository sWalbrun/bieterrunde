<?php

namespace App\Filament\Pages;

use App\Import\ImportProcessor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Page
{
    use WithFileUploads;
    use InteractsWithForms;

    /**
     * Needed for the file upload input to work properly.
     *
     * @var mixed
     */
    public $import = null;

    protected static ?string $navigationIcon = 'bi-filetype-xlsx';

    protected static string $view = 'filament.pages.import';

    public function getActions(): array
    {
        // You can register further templates here
        return [
            Action::make('user template')
                ->label(trans('User template'))
                ->action(
                    fn () => tenancy()->central(
                        fn () => Storage::disk('local')->download('/assets/UserImportTemplate.xlsx')
                    )
                )
            ->icon('heroicon-o-users')
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('import')
                ->label(trans('Import'))
                ->afterStateUpdated(
                    function () {
                        $files = collect($this->import);
                        if ($files->isEmpty()) {
                            return;
                        }
                        Excel::import(
                            resolve(ImportProcessor::class),
                            array_pop($this->import)->getRealPath()
                        );
                    }
                )
        ];
    }
}
