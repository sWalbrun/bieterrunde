<?php

namespace App\Import\Providers;

use App\Import\ModelMapping\MappingRegister;
use App\Import\ModelMapping\RoleMapping;
use App\Import\ModelMapping\UserMapping;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(MappingRegister::class);

        /** @var MappingRegister $register */
        $register = resolve(MappingRegister::class);
        $register
            ->register(new UserMapping())
            ->register(new RoleMapping());
    }
}
