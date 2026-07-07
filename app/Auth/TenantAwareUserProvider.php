<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Stancl\Tenancy\Database\TenantScope;

/**
 * Resolves the authenticated user without the tenant global scope.
 *
 * The user model is tenant scoped (BelongsToTenant), so once a super admin
 * switches to another tenant the default provider can no longer load their
 * own record (their tenant_id no longer matches the active tenant) and would
 * be logged out. Cross-tenant tenant enforcement for regular users still
 * happens in {@link \App\Tenancy\InitializeTenancyByCookie}.
 */
class TenantAwareUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->withoutGlobalScope(TenantScope::class)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        $model = $this->createModel();

        $retrievedModel = $this->newModelQuery($model)
            ->withoutGlobalScope(TenantScope::class)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel
            : null;
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $query = $this->newModelQuery()->withoutGlobalScope(TenantScope::class);

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password') || is_array($value) || $value instanceof \Closure) {
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }
}
