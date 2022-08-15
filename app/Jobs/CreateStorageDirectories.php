<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Since we are using a local storage, we have to create the directories for each tenant.
 */
class CreateStorageDirectories implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        $this->createDirIfNotExisting(base_path('storage/tenant' . $this->tenant->id . '/app/public'));
        $this->createDirIfNotExisting(base_path('storage/tenant' . $this->tenant->id . '/framework/cache'));
        $this->createDirIfNotExisting(base_path('storage/tenant' . $this->tenant->id . '/framework/sessions'));
        $this->createDirIfNotExisting(base_path('storage/tenant' . $this->tenant->id . '/framework/testing'));
        $this->createDirIfNotExisting(base_path('storage/tenant' . $this->tenant->id . '/framework/views'));
    }

    private function createDirIfNotExisting(string $directoryName): void
    {
        if (file_exists($directoryName)) {
            return;
        }

        mkdir($directoryName, 0777, true);
    }
}
