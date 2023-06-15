<?php

use App\BidderRound\EnumTargetAmountReachedStatus;
use App\BidderRound\TargetAmountReachedReport;
use App\Exceptions\NoRoundFoundException;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;

it('returns the round won for success', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(BidderRoundReport::factory())
        ->create();
    $bidderRoundReport = $bidderRound->bidderRoundReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::SUCCESS(), $bidderRound, $bidderRoundReport);
    expect($report->roundWon())->toBe($bidderRoundReport->roundWon);
});

it('returns the format sum amount for success', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(BidderRoundReport::factory())
        ->create();
    $bidderRoundReport = $bidderRound->bidderRoundReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::SUCCESS(), $bidderRound, $bidderRoundReport);
    expect($report->sumAmountFormatted())->toBe($bidderRoundReport->sumAmountFormatted);
});

it('throws for wrong status for sum amount formatted', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(BidderRoundReport::factory())
        ->create();
    $bidderRoundReport = $bidderRound->bidderRoundReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(), $bidderRound, $bidderRoundReport);
    expect(fn () => $report->sumAmountFormatted())->toThrow(NoRoundFoundException::class);
});

it('throws for wrong status for round won', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(BidderRoundReport::factory())
        ->create();
    $bidderRoundReport = $bidderRound->bidderRoundReport;

    $report = new TargetAmountReachedReport(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(), $bidderRound, $bidderRoundReport);
    expect(fn () => $report->roundWon())->toThrow(NoRoundFoundException::class);
});
