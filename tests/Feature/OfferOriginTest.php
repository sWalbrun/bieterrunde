<?php

use App\BidderRound\OfferService;
use App\Enums\ShareValue;
use App\Filament\Resources\TopicResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Livewire\livewire;

function openTopicForOrigin(int $rounds = 1): Topic
{
    return Topic::factory(state: [Topic::COL_ROUNDS => $rounds])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addWeek(),
        ]))->create();
}

it('marks member submitted offers as not entered by an admin', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = openTopicForOrigin();

    (new OfferService)->saveOffers($user, [$topic->id => [1 => 52.0]]);

    expect($user->offersForTopic($topic)->first()->enteredByAdmin)->toBeFalse();
});

it('marks offers as entered by an admin when the service is told so', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = openTopicForOrigin();

    (new OfferService)->saveOffers($user, [$topic->id => [1 => 52.0]], enteredByAdmin: true);

    expect($user->offersForTopic($topic)->first()->enteredByAdmin)->toBeTrue();
});

it('flips the origin back to member when a member overwrites an admin entered offer', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = openTopicForOrigin();

    (new OfferService)->saveOffers($user, [$topic->id => [1 => 52.0]], enteredByAdmin: true);
    (new OfferService)->saveOffers($user, [$topic->id => [1 => 58.0]]);

    $offer = $user->offersForTopic($topic)->first();
    expect($offer->enteredByAdmin)->toBeFalse()
        ->and($offer->amount)->toBe(58.0);
});

it('records offers entered via the admin participant editor as admin entered', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = openTopicForOrigin();
    $topic->users()->attach($user);
    Share::factory()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);

    livewire(UsersRelationManager::class, ['ownerRecord' => $topic])
        ->callTableAction('edit', $user, data: [
            User::COL_EMAIL => $user->email,
            User::COL_NAME => $user->name,
            'offers' => [1 => 80],
        ]);

    $offer = $user->offersForTopic($topic)->where(Offer::COL_ROUND, 1)->first();
    expect($offer)->not->toBeNull()
        ->and($offer->amount)->toBe(80.0)
        ->and($offer->enteredByAdmin)->toBeTrue();
});

it('shows the aggregated offer origins on the edit bidder round page', function () {
    $this->createAndActAsUser();
    $topic = openTopicForOrigin();
    $round = $topic->bidderRound;

    Offer::factory()->count(2)->for($topic)->for(User::factory())->create();
    Offer::factory()->enteredByAdmin()->for($topic)->for(User::factory())->create();

    livewire(\App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound::class, ['record' => $round->id])
        ->assertSee(trans('Offers member / admin'))
        ->assertSee('2 / 1');
});

it('aggregates offer origins across the whole bidder round', function () {
    $topicA = openTopicForOrigin();
    /** @var BidderRound $round */
    $round = $topicA->bidderRound;
    /** @var Topic $topicB */
    $topicB = Topic::factory(state: [Topic::COL_ROUNDS => 1])->for($round)->create();

    // 3 member offers, 2 admin offers spread across the round's topics
    Offer::factory()->count(2)->for($topicA)->for(User::factory())->create();
    Offer::factory()->for($topicB)->for(User::factory())->create();
    Offer::factory()->count(2)->enteredByAdmin()->for($topicB)->for(User::factory())->create();

    expect($round->offerSourceCounts())->toBe(['member' => 3, 'admin' => 2]);
});
