<?php

namespace Tests\Feature\Notification;

use App\Enums\ShareValue;
use App\Jobs\RememberTheBidderRound;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\ReminderOfBidderRound;
use Illuminate\Support\Facades\Notification;

it('Notifies the users with missing offers', function () {
    User::query()->delete();

    /** @var Topic $topic */
    $topic = Topic::factory()
        ->has(Offer::factory()->for(User::factory()))
        ->for(BidderRound::factory())->create();

    /** @var User $userWithOffer */
    $userWithOffer = User::query()->first();
    /** @var Share $share */
    $share = Share::factory(state: [Share::COL_VALUE => ShareValue::ONE])
        ->afterMaking(function (Share $share) use ($topic, $userWithOffer) {
            $share->user()->associate($userWithOffer);
            $share->topic()->associate($topic);
        })->create();
    $share->save();

    /** @var User $userWithoutOffer */
    $userWithoutOffer = User::factory()->afterCreating(function (User $user) use ($topic) {
        $user->shares()->save(
            Share::factory()
                ->afterMaking(function (Share $share) use ($topic, $user) {
                    $share->user()->associate($user);
                    $share->topic()->associate($topic);
                })->create()
        );
    })->create();
    Notification::fake();
    $job = new RememberTheBidderRound($topic->bidderRound);
    $job->handle();
    Notification::assertSentTo(
        $userWithoutOffer,
        ReminderOfBidderRound::class,
        function (ReminderOfBidderRound $reminder) use ($userWithoutOffer) {
            $mailMessage = $reminder->toMail();
            $this->assertEquals("Servus $userWithoutOffer->name", $mailMessage->greeting);

            return $reminder;
        });
    Notification::assertNotSentTo($userWithOffer, ReminderOfBidderRound::class);
});
