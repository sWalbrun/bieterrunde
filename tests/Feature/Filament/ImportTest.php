<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use SWalbrun\FilamentModelImport\Filament\Pages\ImportPage;

use function Pest\Livewire\livewire;

it('can create an user and roles by import', function () {
    $fileToImport = getDefaultXlsx('UserImport.xlsx');

    livewire(ImportPage::class)
        ->fillForm([
            ImportPage::IMPORT => $fileToImport,
        ])
        ->callAction('save')
        ->send();

    /** @var User $importedUser */
    $importedUser = User::query()->where(User::COL_NAME, '=', 'Sebastian')->first();
    expect($importedUser)->not->toBeNull()
        ->and($importedUser->hasRole('admin'))->toBeTruthy()
        ->and($importedUser->hasRole('bidderRoundParticipant'))->toBeTruthy();
});

it('can update an user by import', function () {
    User::query()->create([
        'name' => 'Sebastian12',
        'password' => Hash::make('password!'),
        'email' => 'ws-1993@gmx.de',
    ]);

    $fileToImport = getDefaultXlsx('UserImport.xlsx');
    livewire(ImportPage::class)
        ->fillForm([
            ImportPage::IMPORT => $fileToImport,
        ])
        ->callAction('save')
        ->send();

    /** @var User $importedUser */
    $importedUser = User::query()->where(User::COL_NAME, '=', 'Sebastian')->first();
    expect($importedUser->name)
        ->toBe('Sebastian', 'Value has not been updated according to xlsx file')
        ->and(User::query()->count())->toBe(2, 'Only the logged in user and the updated one must exist');
});

function getDefaultXlsx(string $fileName): UploadedFile
{
    $uploaded = new UploadedFile(
        base_path('tests/assets/'.$fileName),
        $fileName,
        null,
        null,
        true
    );

    // Livewire’s Testable expects a public->name
    $uploaded->name = $fileName;

    return $uploaded;
}
