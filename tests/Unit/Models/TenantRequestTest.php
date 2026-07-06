<?php

use App\Enums\EnumTenantRequestStatus;
use App\Models\TenantRequest;

it('defaults to pending status', function () {
    /** @var TenantRequest $request */
    $request = TenantRequest::factory()->create();

    expect($request->refresh()->status)->toBe(EnumTenantRequestStatus::PENDING)
        ->and($request->isPending())->toBeTrue();
});

it('is not tenant scoped', function () {
    TenantRequest::factory()->count(2)->create();

    // No tenancy initialized — central query must see all requests
    expect(TenantRequest::query()->count())->toBe(2);
});
