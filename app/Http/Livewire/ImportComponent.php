<?php

namespace App\Http\Livewire;

use App\Import\ImportProcessor;
use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WireUi\Traits\Actions;

/**
 * This component takes care of uploading import files. Those files can be downloaded as a template beforehand
 * via this component also.
 */
class ImportComponent extends Component
{
    use Actions;
    use WithFileUploads;

    /**
     * @var TemporaryUploadedFile|string|null
     */
    public $file = null;

    public function render()
    {
        return view('livewire.import-component');
    }

    /**
     * Tries to import the handed over file. Currently, no feedback is given for which models have been created/updated.
     *
     * @return void
     */
    public function import(): void
    {
        $this->validate(
            ['file' => ['required', 'file']]
        );

        try {
            Excel::import(resolve(ImportProcessor::class), $this->file->path());
        } catch (Exception $exception) {
            $this->addError('invalidFile', trans('Die Datei scheint nicht gültig zu sein'));
            return;
        }
        $this->dialog()->confirm(
            [
                'title' => trans('Der Upload wurde erfolgreich durchgeführt! Die Daten wurden neu angelegt bzw. aktualisiert'),
                'acceptLabel' => trans('Ok'),
                'method' => 'reload'
            ]
        );
    }

    public function reload()
    {
        return redirect(request()->header('Referer'));
    }

    /**
     * Returns all templates that can be filled with data to be uploaded again.
     * So the user does not have to worry about correct header lines.
     *
     * @return array<string, string> The key is the translation whereas the value represents the method which returns the file
     */
    public function templates(): array
    {
        return [
            trans('Benutzer und Rollen') => 'users',
            // You can add further templates here
        ];
    }

    public function users(): StreamedResponse
    {
        return tenancy()->central(fn () => Storage::disk('local')->download('/assets/UserImportTemplate.xlsx'));
    }
}
