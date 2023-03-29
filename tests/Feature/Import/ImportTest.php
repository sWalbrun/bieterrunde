<?php

use App\Filament\Pages\Import;
use App\Import\ModelMapping\AssociationRegister;
use App\Import\ModelMapping\IdentificationRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Import\ModelMappings\IdentificationOfBlog;
use Tests\Feature\Import\ModelMappings\IdentificationOfPost;
use function Pest\Livewire\livewire;

it('can create an user and roles by import', function () {
    $fileToImport = getDefaultXlsx('UserImport.xlsx');

    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ])->send();

    /** @var User $importedUser */
    $importedUser = User::query()->where(User::COL_NAME, '=', 'Sebastian')->first();
    expect($importedUser)->not->toBeNull()
        ->and($importedUser->hasRole('admin'))->toBeTruthy()
        ->and($importedUser->hasRole('user'))->toBeTruthy();
});

it('can update an user by import', function () {
    User::query()->create([
        'name' => 'Sebastian12',
        'password' => Hash::make('password!'),
        'email' => 'ws-1993@gmx.de'
    ]);

    $fileToImport = getDefaultXlsx('UserImport.xlsx');
    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ])->send();

    /** @var User $importedUser */
    $importedUser = User::query()->where(User::COL_NAME, '=', 'Sebastian')->first();
    expect($importedUser->name)
        ->toBe('Sebastian', 'Value has not been updated according to xlsx file')
        ->and(User::query()->count())->toBe(2, 'Only the logged in user and the updated one must exist');
});


it('does not call the relation hook if the method argument types do not match', function () {
    $blogMock = mockBlock();
    $postMock = mockPost();

    /** @var IdentificationRegister $identificationRegister */
    $identificationRegister = resolve(IdentificationRegister::class);
    $identificationRegister
        ->register(new IdentificationOfBlog($blogMock))
        ->register(new IdentificationOfPost($postMock));

    /** @var AssociationRegister $associationRegister */
    $associationRegister = resolve(AssociationRegister::class);
    $associationRegister->registerClosure(
        fn (stdClass $post, IdentificationOfBlog $blog) => IdentificationOfPost::$hasHookBeenCalled = true
    );

    $fileToImport = getDefaultXlsx('UserImport.xlsx');
    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ])->send();

    expect(IdentificationOfPost::$hasHookBeenCalled)->toBeFalsy();
});

it('does call the relation hook if the method argument types match', function () {
    $this->markTestSkipped('Create mocks and test');
    $blogMock = mockBlock();
    $postMock = mockPost();

    /** @var IdentificationRegister $identificationRegister */
    $identificationRegister = resolve(IdentificationRegister::class);
    $identificationRegister
        ->register(new IdentificationOfBlog($blogMock))
        ->register(new IdentificationOfPost($postMock));

    /** @var AssociationRegister $associationRegister */
    $associationRegister = resolve(AssociationRegister::class);
    $associationRegister->registerClosure(
        fn (stdClass $post, IdentificationOfBlog $blog) => IdentificationOfPost::$hasHookBeenCalled = true
    );

    $fileToImport = getDefaultXlsx('UserImport.xlsx');
    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ])->send();

    expect(IdentificationOfPost::$hasHookBeenCalled)->toBeFalsy();
});

function getDefaultXlsx(string $fileName): UploadedFile
{
    return new UploadedFile(
        base_path('tests/assets/' . $fileName),
        $fileName,
        null,
        null,
        true
    );
}

function mockBlock(): Model
{
    $blogMock = Mockery::mock(Model::class)->makePartial();
    $blogMock->shouldReceive('save')->andReturn(true);
    $blogMock->shouldReceive('newInstance')->andReturn($blogMock);
    $blogMock->shouldReceive('getAttributes')->passthru();
    $blogBuilderMock = Mockery::mock(Builder::class);
    $blogBuilderMock->shouldReceive('updateOrCreate')->andReturn($blogMock);
    $blogMock->shouldReceive('newQuery')
        ->andReturn(
            $blogBuilderMock
        );
    return $blogMock;
}

function mockPost(): Model
{
    $postMock = Mockery::mock(Model::class)->makePartial();
    $postMock->shouldReceive('save')->andReturn(true);
    $postMock->shouldReceive('newInstance')->andReturn($postMock);
    $postMock->shouldReceive('getAttributes')->passthru();
    $postMock->fillable(['property']);
    $postBuilderMock = Mockery::mock(Builder::class);
    $postBuilderMock->shouldReceive('firstOrNew')->andReturn($postMock);
    $postMock->shouldReceive('newQuery')->andReturn($postBuilderMock);
    return $postMock;
}
