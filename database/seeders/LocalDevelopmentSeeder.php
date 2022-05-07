<?php

namespace Database\Seeders;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
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
        $this->seedAdmin();
        if (User::query()->count() > 1) {

            // we do not seed in case there are already users available
            return;
        }
        $this->seedBidderRoundParticipants($this->seedBidderRound());
    }

    private function seedAdmin(): void
    {
        $email = 'admin@solawi.de';
        $user = User::query()->where(User::COL_EMAIL, '=', $email)->first();

        $user ??= new User();
        $user->email = $email;
        $user->password = Hash::make('LetMeIn');
        $user->name = 'Admin';
        $user->email_verified_at = Carbon::now();
        $user->attachRole(User::ROLE_ADMIN);
        $user->attachRole(User::ROLE_BIDDER_ROUND_PARTICIPANT);
        $user->save();
    }

    private function seedBidderRoundParticipants(BidderRound $bidderRound)
    {
        User::factory()
            ->count(self::USER_COUNT)
            ->create()
            ->each(function (User $user) use ($bidderRound) {
                $user->attachRole(User::ROLE_BIDDER_ROUND_PARTICIPANT);
                OfferFactory::reset();
                OfferFactory::randomize();
                Offer::factory()
                    ->count(self::OFFER_COUNT)
                    ->create()
                    ->each(function (Offer $offer) use ($user, $bidderRound) {
                        $offer->bidderRound()->associate($bidderRound);
                        $offer->user()->associate($user)->save();
                    });
            });
    }

    private function seedBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => 68_000,
            BidderRound::COL_COUNT_OFFERS => 3,
        ]);
    }
}
