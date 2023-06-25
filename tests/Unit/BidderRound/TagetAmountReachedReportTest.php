<?php

use App\BidderRound\TargetAmountReachedReport;
use App\Enums\EnumTargetAmountReachedStatus;
use App\Exceptions\NoRoundFoundException;
use App\Models\BidderRound;
use App\Models\Topic;
use App\Models\TopicReport;

it('returns the round won for success', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->for(BidderRound::factory())
        ->has(TopicReport::factory())
        ->create();
    $topicReport = $topic->topicReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::SUCCESS(), $topic, $topicReport);
    expect($report->roundWon())->toBe($topicReport->roundWon);
});

it('returns the format sum amount for success', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->for(BidderRound::factory())
        ->has(TopicReport::factory())
        ->create();
    $topicReport = $topic->topicReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::SUCCESS(), $topic, $topicReport);
    expect($report->sumAmountFormatted())->toBe($topicReport->sumAmountFormatted);
});

it('throws for wrong status for sum amount formatted', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->for(BidderRound::factory())
        ->has(TopicReport::factory())
        ->create();
    $topicReport = $topic->topicReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(), $topic, $topicReport);
    expect(fn () => $report->sumAmountFormatted())->toThrow(NoRoundFoundException::class);
});

it('throws for wrong status for round won', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->for(BidderRound::factory())
        ->has(TopicReport::factory())
        ->create();
    $topicReport = $topic->topicReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(), $topic, $topicReport);
    expect(fn () => $report->roundWon())->toThrow(NoRoundFoundException::class);
});
