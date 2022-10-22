<?php

namespace App\Import\Providers;

use App\Import\ModelMapping\AssociationRegister;
use App\Import\ModelMapping\IdentificationRegister;
use App\Import\ModelMapping\IdentificationOfRole;
use App\Import\ModelMapping\IdentificationOfUser;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use jeremykenedy\LaravelRoles\Models\Role;

class ImportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $identificationOfUser = new IdentificationOfUser();

        $this->app->singleton(IdentificationRegister::class);
        // phpcs:ignore
        /** @var IdentificationRegister $identificationRegister */
        $identificationRegister = resolve(IdentificationRegister::class);
        $identificationRegister
            ->register($identificationOfUser)
            ->register(new IdentificationOfRole());

        $this->app->singleton(AssociationRegister::class);

        // phpcs:ignore
        /** @var AssociationRegister $associationRegister */
        $associationRegister = resolve(AssociationRegister::class);
        $associationRegister->registerAssociationOf($identificationOfUser);
    }
}
