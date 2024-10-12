<?php

namespace Database\Seeders;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Tenant;
use App\Models\Topic;
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
        Role::findOrCreate(config('filament-shield.super_admin.name'));
        Role::findOrCreate(config('filament-shield.filament_user.name'));

        $this->seedAdmin();
        if (User::query()->count() > 1) {

            // we do not seed in case there are already users available
            return;
        }
        /** @var Topic $topic */
        $topic = Topic::factory(state: [Topic::COL_ROUNDS => 3, Topic::COL_TARGET_AMOUNT => 68_000])
            ->for(BidderRound::factory(state: [
                BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
                BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
            ]))->create();
        $this->seedTopicParticipants($topic);
    }

    private function seedAdmin(): void
    {

        $email = sprintf('admin%s@solawi.de', tenant()->id);
        $user = User::query()->where(User::COL_EMAIL, '=', $email)->first();

        $user ??= new User;
        $user->email = $email;
        $user->password = Hash::make('password');
        $user->name = 'Admin';
        $user->email_verified_at = Carbon::now();
        $user->contributionGroup = EnumContributionGroup::FULL_MEMBER();
        $user->save();
        $user->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name')));
        $user->save();
    }

    private function seedTopicParticipants(Topic $topic)
    {
        User::factory()
            ->count(self::USER_COUNT)
            ->create()
            ->each(function (User $user) use ($topic) {
                Share::factory(state: [
                    Share::COL_FK_USER => $user->id,
                    Share::COL_FK_TOPIC => $topic->id,
                ])->create();
                OfferFactory::reset();
                OfferFactory::randomize();
                Offer::factory()
                    ->count(self::OFFER_COUNT)
                    ->create()
                    ->each(function (Offer $offer) use ($user, $topic) {
                        $offer->topic()->associate($topic);
                        $offer->user()->associate($user)->save();
                    });
            });
    }
}
