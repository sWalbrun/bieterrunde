<?php

use __Tests__\Models\Blog;
use __Tests__\Models\Post;
use App\Filament\Pages\Import;
use App\Import\ModelMapping\AssociationRegister;
use App\Import\ModelMapping\IdentificationOf;
use App\Import\ModelMapping\IdentificationRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
    $blog = mockBlog();
    $post = mockPost();

    /** @var IdentificationRegister $identificationRegister */
    $identificationRegister = resolve(IdentificationRegister::class);
    $identificationRegister
        ->register(new IdentificationOfBlog($blog))
        ->register(new IdentificationOfPost($post));

    /** @var AssociationRegister $associationRegister */
    $associationRegister = resolve(AssociationRegister::class);
    $associationRegister->registerClosure(
        fn (stdClass $post, IdentificationOfBlog $blog) => IdentificationOfPost::$hasHookBeenCalled = true
    );

    $fileToImport = getDefaultXlsx('PropertyImport.xlsx');
    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ]);
    expect(IdentificationOfPost::$hasHookBeenCalled)->toBeFalsy();
});

it('does call the relation hook if the method argument types match', function () {
    $blog = mockBlog();
    $post = mockPost();

    /** @var IdentificationRegister $identificationRegister */
    $identificationRegister = resolve(IdentificationRegister::class);
    $identificationRegister
        ->register(new IdentificationOfBlog($blog))
        ->register(new IdentificationOfPost($post));

    /** @var AssociationRegister $associationRegister */
    $associationRegister = resolve(AssociationRegister::class);
    $associationRegister->registerClosure(
        fn (Post $post, Blog $blog) => IdentificationOfPost::$hasHookBeenCalled = true
    );

    $fileToImport = getDefaultXlsx('PropertyImport.xlsx');
    livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ]);
    expect(IdentificationOfPost::$hasHookBeenCalled)->toBeTruthy();
});

it('throws an exception for ', function (Closure $modelMapping) {
    /** @var IdentificationRegister $register */
    $register = resolve(IdentificationRegister::class);
    $register->register($modelMapping());

    $fileToImport = getDefaultXlsx('UserImport.xlsx');
    expect(fn() => livewire(Import::class)
        ->fillForm([
            Import::IMPORT => [uuid_create() => $fileToImport]
        ])->send())->toThrow(Exception::class, "The regex's result is overlapping");
})->with([
    'regex matching between two models' =>
        fn () => new class extends IdentificationOf {
            public function __construct()
            {
                parent::__construct(new User());
            }

            public function propertyMapping(): Collection
            {
                return collect([
                    'matchAll' => '/.*/i'
                ]);
            }

            public function uniqueColumns(): array
            {
                return [];
            }
        }
    ,
    'regex matching within same model' =>
        fn () => new class extends IdentificationOf {
            public function __construct()
            {
                parent::__construct(new User());
            }

            public function propertyMapping(): Collection
            {
                return collect([
                    'productNumber' => '/Product Number/i',
                    'userNumber' => '/Number/i'
                ]);
            }

            public function uniqueColumns(): array
            {
                return [];
            }
        }

]);

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

function mockPost(): Post
{
    $postMock = Mockery::mock(Post::class)->makePartial();
    $postMock->shouldReceive('save')->andReturn(true);
    $postMock->shouldReceive('newInstance')->andReturn($postMock);
    $postMock->shouldReceive('getAttributes')->passthru();
    $postMock->fillable(['property']);
    $postBuilderMock = Mockery::mock(Builder::class);
    $postBuilderMock->shouldReceive('firstOrNew')->andReturn($postMock);
    $postMock->shouldReceive('newQuery')->andReturn($postBuilderMock);
    return $postMock;
}

function mockBlog(): Blog
{
    $blogMock = Mockery::mock(Blog::class)->makePartial();
    $blogMock->shouldReceive('save')->andReturn(true);
    $blogMock->shouldReceive('newInstance')->andReturn($blogMock);
    $blogMock->shouldReceive('getAttributes')->passthru();
    $blogMock->shouldReceive('fill');
    $blogBuilderMock = Mockery::mock(Builder::class);
    $blogBuilderMock->shouldReceive('firstOrNew')->andReturn($blogMock);
    $blogBuilderMock->shouldReceive('updateOrCreate')->andReturn($blogMock);
    $blogMock->shouldReceive('newQuery')
        ->andReturn(
            $blogBuilderMock
        );
    return $blogMock;
}




