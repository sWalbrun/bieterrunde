<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * This test takes care of all methods and business logic of the {@link User}.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testIsNewMember()
    {
        /** @var User $user */
        $user = User::factory()->create()->first();
        $user->joinDate = Carbon::now();

        $this->assertTrue($user->isNewMember);
    }

    public function testOffers()
    {
        $user = $this->createAndActAsUser();

        $offers = $this->createOffers($user);

        $this->assertCount(7, $user->offers);

        $this->assertSame($offers->first()->{BaseModel::COL_ID}, $user->offers->first()->{BaseModel::COL_ID});
    }

    public function testOffersForRound()
    {
        $user = $this->createAndActAsUser();

        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::factory()->create()->first();

        $this->assertEmpty($user->offersForRound($bidderRound)->get());

        $offers = $this->createOffers($user, $bidderRound);
        $this->assertNotEmpty($user->offersForRound($bidderRound)->get());
        $this->assertEquals($offers->count(), $offers->intersect($user->offersForRound($bidderRound)->get())->count());
    }

    public function testBidderRoundWithRelations()
    {
        $user = $this->createAndActAsUser();
        $user->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT));

        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::factory()->create()->first();

        $this->createOffers($user, $bidderRound);

        /** @var Collection<User> $users */
        $users = User::bidderRoundWithRelations($bidderRound->id);

        $this->assertEquals(1, $users->count());

        /** @var User $user */
        $user = $users->first();

        $this->assertNotNull($user->getRelation('offers'), 'No offers have been loaded');
        /** @var Offer $offer */
        $offer = $user->offers->first();

        $this->assertNotNull($offer->getRelation('bidderRound'));
    }

    private function createOffers(User $user, ?BidderRound $bidderRound = null): Collection
    {
        return Offer::factory()
            ->count(7)
            ->make()
            ->each(function (Offer $offer) use ($user, $bidderRound) {
                $offer->user()->associate($user)->save();
                if (isset($bidderRound)) {
                    $offer->bidderRound()->associate($bidderRound)->save();
                }
            });
    }
}
