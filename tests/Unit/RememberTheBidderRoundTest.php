<?php

namespace Tests\Unit;

use App\BidderRound\Participant;
use App\Jobs\RememberTheBidderRound;
use App\Models\BidderRound;
use App\Models\User;
use App\Notifications\ReminderOfBidderRound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * This test takes care of sending the notifications of {@link RememberTheBidderRound} only to the {@link Participant participants}
 * which did not make the offers yet.
 */
class RememberTheBidderRoundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * This test makes sure a participant without offers gets notified and one with offers does not get notified.
     */
    public function testNotifyParticipants()
    {
        Notification::fake();

        $round = $this->createBidderRound();
        $participantWithOffers = $this->createParticipantWithOffers($round);
        $participantWithoutOffers = $this->createParticipantWithoutOffers();

        $job = new RememberTheBidderRound($round);
        $job->handle();

        Notification::assertSentTo(
            $participantWithoutOffers,
            ReminderOfBidderRound::class,
            function (ReminderOfBidderRound $reminder) use ($participantWithoutOffers) {
                $mailMessage = $reminder->toMail();
                $this->assertEquals("Servus $participantWithoutOffers->name", $mailMessage->greeting);

                return $reminder;
            });
        Notification::assertNotSentTo($participantWithOffers, ReminderOfBidderRound::class);
    }

    private function createParticipantWithOffers(BidderRound $round): User
    {
        $participantWithOffers = $this->createAndActAsUser();
        $participantWithOffers->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT));
        $this->createOffers($participantWithOffers, $round);

        return $participantWithOffers;
    }

    private function createParticipantWithoutOffers(): User
    {
        $participantWithoutOffers = $this->createAndActAsUser();
        $participantWithoutOffers->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT));

        return $participantWithoutOffers;
    }
}
