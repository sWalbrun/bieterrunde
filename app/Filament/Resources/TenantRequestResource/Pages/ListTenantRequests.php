<?php

namespace App\Filament\Resources\TenantRequestResource\Pages;

use App\Filament\Resources\TenantRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListTenantRequests extends ListRecords
{
    protected static string $resource = TenantRequestResource::class;
}
