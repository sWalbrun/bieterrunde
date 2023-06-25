<?php

use App\BidderRound\TargetAmountReachedReport;
use App\BidderRound\TopicService;
use App\Enums\EnumTargetAmountReachedStatus;
use App\Exceptions\NoRoundFoundException;
use App\Filament\Resources\TopicResource\Pages\EditTopic;
use App\Filament\Resources\TopicResource\RelationManagers\TopicReportRelationManager;
use App\Filament\Resources\TopicResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Livewire\Livewire;

beforeAll(fn () => Resource::ignorePolicies());

it('shows the active members', function () {
    $activeUsers = User::factory()->count(2)->create([
        User::COL_JOIN_DATE => now()->subDay(),
        User::COL_EXIT_DATE => now()->addDay(),
    ]);

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $topic,
    ])->assertCanSeeTableRecords($activeUsers);
});

it('does not show former members', function () {
    $veterans = User::factory()->count(2)->create([
        User::COL_EXIT_DATE => now()->subDay(),
    ]);

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $topic,
    ])->assertCanNotSeeTableRecords($veterans);
});

it('successfully calculates a report', function () {

    $reportMock = Mockery::mock(TargetAmountReachedReport::class)->makePartial();
    $reportMock->status = EnumTargetAmountReachedStatus::SUCCESS();
    $reportMock->shouldReceive('sumAmountFormatted')->andReturn('wayne');
    $reportMock->shouldReceive('roundWon')->andReturn(1);
    $serviceMock = Mockery::mock(TopicService::class);
    $serviceMock->shouldReceive('calculateReportForTopic')->andReturn($reportMock);
    $this->instance(
        TopicService::class,
        $serviceMock
    );

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    Livewire::test(EditTopic::class, ['record' => $topic->id])
        ->callPageAction(EditTopic::CALCULATE_BIDDER_ROUND_ACTION)
        ->assertHasNoErrors()
        ->assertNotified(
            Notification::make()
                ->success()
                ->title(trans('Es konnte eine Runde ermittelt werden!'))
                ->body(trans('Bieterrunde 1 mit dem Betrag wayne€ deckt die Kosten'))
        );
});

it('successfully reports about the existing report', function () {

    $reportMock = Mockery::mock(TargetAmountReachedReport::class)->makePartial();
    $reportMock->status = EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED();
    $reportMock->shouldReceive('sumAmountFormatted')->andReturn('wayne');
    $reportMock->shouldReceive('roundWon')->andReturn(1);
    $serviceMock = Mockery::mock(TopicService::class);
    $serviceMock->shouldReceive('calculateReportForTopic')->andReturn($reportMock);
    $this->instance(
        TopicService::class,
        $serviceMock
    );

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    Livewire::test(EditTopic::class, ['record' => $topic->id])
        ->callPageAction(EditTopic::CALCULATE_BIDDER_ROUND_ACTION)
        ->assertHasNoErrors()
        ->assertNotified(
            Notification::make()
                ->success()
                ->title(trans('Die Runde wurde bereits ermittelt!'))
                ->body(trans('Bieterrunde 1 mit dem Betrag wayne€ deckt die Kosten'))
        );
});

it('warns about missing offers', function () {

    $reportMock = Mockery::mock(TargetAmountReachedReport::class)->makePartial();
    $reportMock->status = EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN();
    $serviceMock = Mockery::mock(TopicService::class);
    $serviceMock->shouldReceive('calculateReportForTopic')->andReturn($reportMock);
    $this->instance(
        TopicService::class,
        $serviceMock
    );

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    Livewire::test(EditTopic::class, ['record' => $topic->id])
        ->callPageAction(EditTopic::CALCULATE_BIDDER_ROUND_ACTION)
        ->assertHasNoErrors()
        ->assertNotified(
            Notification::make()
                ->warning()
                ->title(trans('Es wurden noch nicht alle Gebote abgegeben!'))
        );
});

it('warns about insufficient money', function () {

    $reportMock = Mockery::mock(TargetAmountReachedReport::class)->makePartial();
    $reportMock->status = EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY();
    $serviceMock = Mockery::mock(TopicService::class);
    $serviceMock->shouldReceive('calculateReportForTopic')->andReturn($reportMock);
    $this->instance(
        TopicService::class,
        $serviceMock
    );

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    Livewire::test(EditTopic::class, ['record' => $topic->id])
        ->callPageAction(EditTopic::CALCULATE_BIDDER_ROUND_ACTION)
        ->assertHasNoErrors()
        ->assertNotified(
            Notification::make()
                ->danger()
                ->title(trans('Leider konnte mit keiner einzigen Runde der Zielbetrag ermittelt werden.')));
});

it('warns about error', function () {

    $reportMock = Mockery::mock(TargetAmountReachedReport::class)->makePartial();
    $reportMock->status = EnumTargetAmountReachedStatus::SUCCESS();
    $reportMock->shouldReceive('roundWon')->andThrow(NoRoundFoundException::class);
    $serviceMock = Mockery::mock(TopicService::class);
    $serviceMock->shouldReceive('calculateReportForTopic')->andReturn($reportMock);
    $this->instance(
        TopicService::class,
        $serviceMock
    );

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    Livewire::test(EditTopic::class, ['record' => $topic->id])
        ->callPageAction(EditTopic::CALCULATE_BIDDER_ROUND_ACTION)
        ->assertHasNoErrors()
        ->assertNotified(
            Notification::make()
                ->title(trans('Es ist ein unerwarteter Fehler aufgetreten'))
                ->danger());
});

it('informs the participants about the found round', function () {
    \Illuminate\Support\Facades\Notification::fake();

    /** @var Topic $topic */
    $topic = Topic::factory()
        ->for(BidderRound::factory())
        ->has(User::factory())
        ->has(Offer::factory()->state([Offer::COL_ROUND => 2]))
        ->has(TopicReport::factory()->state([
            TopicReport::COL_COUNT_PARTICIPANTS => 1,
            TopicReport::COL_ROUND_WON => 2,
        ]))
        ->createQuietly();
    $topic->users->each(fn (User $user) => $user->offers()->saveMany($topic->offers));

    Livewire::test(
        TopicReportRelationManager::class, [
            'ownerRecord' => $topic,
        ]
    )
        ->callTableAction(TopicReportRelationManager::INFORM_PARTICIPANTS, $topic->topicReport)
        ->assertHasNoErrors();
    $topic->users->each(fn (User $user) => \Illuminate\Support\Facades\Notification::assertSentTo($topic->users->first(), BidderRoundFound::class));
});
