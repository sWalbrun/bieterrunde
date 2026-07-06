<?php

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Tenant;
use App\Models\Topic;
use App\Models\User;

it('deletes all rows of the tenant but keeps other tenants intact', function () {
    /** @var Tenant $doomed */
    $doomed = Tenant::query()->create([Tenant::COL_ID => 'doomed']);
    /** @var Tenant $survivor */
    $survivor = Tenant::query()->create([Tenant::COL_ID => 'survivor']);

    $seed = function (Tenant $tenant) {
        return $tenant->run(function () {
            /** @var User $user */
            $user = User::factory()->create();
            /** @var Topic $topic */
            $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
                ->for(BidderRound::factory(state: [
                    BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
                    BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
                ]))->create();
            Offer::factory()->create([
                Offer::COL_FK_USER => $user->id,
                Offer::COL_FK_TOPIC => $topic->id,
                Offer::COL_ROUND => 1,
            ]);

            return $user;
        });
    };

    $seed($doomed);
    $survivingUser = $seed($survivor);

    $doomed->delete();

    expect(User::query()->withoutGlobalScopes()->where(User::COL_FK_TENANT, 'doomed')->count())->toBe(0)
        ->and(BidderRound::query()->withoutGlobalScopes()->where('tenant_id', 'doomed')->count())->toBe(0)
        ->and(User::query()->withoutGlobalScopes()->where(User::COL_FK_TENANT, 'survivor')->count())->toBeGreaterThan(0)
        ->and($survivingUser->offers()->count())->toBe(1);
});
