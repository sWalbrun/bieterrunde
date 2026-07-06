<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Tenancy\TenantProvisioner;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TenantProvisioner::class)->provision(
            $data['id'],
            $data['admin_name'],
            $data['admin_email'],
        );
    }
}
