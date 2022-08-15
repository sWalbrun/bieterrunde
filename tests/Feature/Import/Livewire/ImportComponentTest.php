<?php

namespace Tests\Feature\Import\Livewire;

use App\Http\Livewire\ImportComponent;
use App\Models\BidderRound;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Those tests make sure the bidder round form is working properly for creating and editing a {@link BidderRound}.
 */
class ImportComponentTest extends TestCase
{
    /**
     * Tests for a successful import of a user.
     *
     * @return void
     */
    public function testSuccessfullyImport(): void
    {
        $this->createAndActAsUser();
        $fileName = 'UserImport.xlsx';
        $file = new UploadedFile(
            base_path('tests/assets/' . $fileName),
            $fileName,
            null,
            null,
            true
        );

        // This line is needed since the testing with livewire is a little buggy
        $file->name = $fileName;
        Livewire::test(ImportComponent::class)
            ->set('file', $file)
            ->call('import')
            ->assertSuccessful();

        $this->assertTrue(User::query()->where('email', 'ws-1993@gmx.de')->exists());
    }

    public function fileProvider(): array
    {
        return [
            'User Template' => [
                'users',
                'UserImportTemplate.xlsx'
            ]
        ];
    }

    /**
     * Tests for a successful download of a template file.
     * @dataProvider fileProvider()
     */
    public function testDownloadUserTemplate(string $methodName, string $fileName): void
    {
        $this->createAndActAsUser();

        Livewire::test(ImportComponent::class)
            ->call($methodName)
            ->assertFileDownloaded($fileName);
    }

    /**
     * This test ensure the dialog is acting correctly even if no valid file has been given.
     *
     * @dataProvider provideInvalidFiles()
     */
    public function testImportWithInvalidFile($fileName): void
    {
        $this->createAndActAsUser();
        $file = null;
        if (isset($fileName) && file_exists(base_path('tests/assets/' . $fileName))) {
            $file = new UploadedFile(
                base_path('tests/assets/' . $fileName),
                $fileName,
                null,
                null,
                true
            );
            // This line is needed since the testing with livewire is a little buggy
            $file->name = $fileName;
        }

        Livewire::test(ImportComponent::class)
            ->set('file', $file)
            ->call('import')
            ->assertHasErrors();
    }

    public function provideInvalidFiles(): array
    {
        return [
            'No file' => [null],
            'File not existing' => ['NonExisting.xlsx'],
            'No Excel file' => ['dogo.png']
        ];
    }
}
