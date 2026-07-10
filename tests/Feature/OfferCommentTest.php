<?php

use App\Enums\EnumPaymentInterval;
use App\Enums\ShareValue;
use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
use App\Filament\Resources\BidderRoundResource\RelationManagers\CommentsRelationManager;
use App\Livewire\OfferForm;
use App\Models\BidderRound;
use App\Models\BidderRoundComment;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Livewire\livewire;

function openTopicWithShare(User $user): Topic
{
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addWeek(),
        ]))->create();

    Share::factory()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);

    return $topic;
}

it('lets a member submit a comment together with the offers', function () {
    $user = $this->createAndActAsUser();
    $topic = openTopicWithShare($user);

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->set('comment', 'Bitte mehr Tomaten!')
        ->call('save')
        ->assertHasNoErrors();

    $comment = BidderRoundComment::query()
        ->where(BidderRoundComment::COL_FK_USER, '=', $user->id)
        ->where(BidderRoundComment::COL_FK_BIDDER_ROUND, '=', $topic->bidderRound->id)
        ->first();
    expect($comment)->not->toBeNull()
        ->and($comment->comment)->toBe('Bitte mehr Tomaten!');
});

it('pre-fills an existing comment when the form is opened again', function () {
    $user = $this->createAndActAsUser();
    $topic = openTopicWithShare($user);
    BidderRoundComment::factory()->create([
        BidderRoundComment::COL_FK_USER => $user->id,
        BidderRoundComment::COL_FK_BIDDER_ROUND => $topic->bidderRound->id,
        BidderRoundComment::COL_COMMENT => 'Servus',
    ]);

    livewire(OfferForm::class)
        ->assertSet('comment', 'Servus');
});

it('removes the comment when it is cleared', function () {
    $user = $this->createAndActAsUser();
    $topic = openTopicWithShare($user);
    BidderRoundComment::factory()->create([
        BidderRoundComment::COL_FK_USER => $user->id,
        BidderRoundComment::COL_FK_BIDDER_ROUND => $topic->bidderRound->id,
        BidderRoundComment::COL_COMMENT => 'weg damit',
    ]);

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->set('comment', '   ')
        ->call('save')
        ->assertHasNoErrors();

    expect(BidderRoundComment::query()->count())->toBe(0);
});

it('shows the comments in the admin relation manager', function () {
    $this->createAndActAsUser();
    /** @var User $member */
    $member = User::factory()->create([User::COL_NAME => 'Maria Muster']);
    $topic = openTopicWithShare($member);
    $comment = BidderRoundComment::factory()->create([
        BidderRoundComment::COL_FK_USER => $member->id,
        BidderRoundComment::COL_FK_BIDDER_ROUND => $topic->bidderRound->id,
        BidderRoundComment::COL_COMMENT => 'Danke fürs Gemüse',
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $topic->bidderRound,
        'pageClass' => EditBidderRound::class,
    ])
        ->assertCanSeeTableRecords([$comment])
        ->assertSee('Maria Muster')
        ->assertSee('Danke fürs Gemüse');
});

it('cascades comments when the bidder round is deleted', function () {
    /** @var User $member */
    $member = User::factory()->create();
    $topic = openTopicWithShare($member);
    $round = $topic->bidderRound;
    BidderRoundComment::factory()->create([
        BidderRoundComment::COL_FK_USER => $member->id,
        BidderRoundComment::COL_FK_BIDDER_ROUND => $round->id,
    ]);

    $round->delete();

    expect(BidderRoundComment::query()->count())->toBe(0);
});
