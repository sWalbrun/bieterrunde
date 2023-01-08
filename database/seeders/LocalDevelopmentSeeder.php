<?php

namespace Database\Seeders;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class LocalDevelopmentSeeder extends Seeder
{
    public const OFFER_COUNT = 3;
    public const USER_COUNT = 120;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (tenant() === null) {
            Tenant::query()->updateOrCreate(['id' => 'foo']);
            Tenant::query()->updateOrCreate(['id' => 'bar']);
            return;
        }

        $this->seedAdmin();
        if (User::query()->count() > 1) {

            // we do not seed in case there are already users available
            return;
        }
        $this->seedBidderRoundParticipants($this->seedBidderRound());
    }

    private function seedAdmin(): void
    {

        $email = sprintf("admin%s@solawi.de", tenant()->id);
        $user = User::query()->where(User::COL_EMAIL, '=', $email)->first();

        $user ??= new User();
        $user->email = $email;
        $user->password = Hash::make('password');
        $user->name = 'Admin';
        $user->email_verified_at = Carbon::now();
        $user->contributionGroup = EnumContributionGroup::FULL_MEMBER();
        $user->countShares = 1;
        $user->save();
        $user->assignRole(Role::findOrCreate(User::ROLE_ADMIN));
        $user->save();
    }

    private function seedBidderRoundParticipants(BidderRound $bidderRound)
    {
        User::factory()
            ->count(self::USER_COUNT)
            ->create()
            ->each(function (User $user) use ($bidderRound) {
                OfferFactory::reset();
                OfferFactory::randomize();
                Offer::factory()
                    ->count(self::OFFER_COUNT)
                    ->create()
                    ->each(function (Offer $offer) use ($user, $bidderRound) {
                        $user->bidderRounds()->syncWithoutDetaching($bidderRound);
                        $offer->bidderRound()->associate($bidderRound);
                        $offer->user()->associate($user)->save();
                    });
            });
    }

    private function seedBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => now()->startOfYear(),
            BidderRound::COL_VALID_TO => now()->startOfYear(),
            BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
            BidderRound::COL_TARGET_AMOUNT => 68_000,
            BidderRound::COL_COUNT_OFFERS => 3,
        ]);
    }

}
