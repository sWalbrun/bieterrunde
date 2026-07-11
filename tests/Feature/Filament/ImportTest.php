<?php

use App\Enums\EnumRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use SWalbrun\FilamentModelImport\Filament\Pages\ImportPage;

use function Pest\Livewire\livewire;

beforeEach(fn () => Storage::fake('tmp-for-tests'));

it('imports new users as members regardless of the file', function () {
    $fileToImport = getDefaultXlsx('UserImport.xlsx');

    livewire(ImportPage::class)
        ->fillForm([
            ImportPage::IMPORT => $fileToImport,
        ])
        ->callAction('save')
        ->send();

    /** @var User $importedUser */
    $importedUser = User::query()->where(User::COL_NAME, '=', 'Sebastian')->first();
    // Any 'Rolle' column in the xlsx is ignored — new users are always members (issue #7)
    expect($importedUser)->not->toBeNull()
        ->and($importedUser->role)->toBe(EnumRole::MEMBER);
});

it('does not demote an existing user when re-imported', function () {
    /** @var User $existing */
    $existing = User::query()->create([
        'name' => 'Sebastian12',
        'password' => Hash::make('password!'),
        'email' => 'ws-1993@gmx.de',
        'role' => EnumRole::ADMIN,
    ]);

    livewire(ImportPage::class)
        ->fillForm([
            ImportPage::IMPORT => getDefaultXlsx('UserImport.xlsx'),
        ])
        ->callAction('save')
        ->send();

    expect($existing->refresh()->role)->toBe(EnumRole::ADMIN);
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
