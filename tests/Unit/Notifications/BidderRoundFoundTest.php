<?php

use App\Models\BidderRound;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Notifications\BidderRoundFound;

it('builds a fully qualified mail', function () {
    $roundWon = 1;
    /** @var TopicReport $report */
    $report = TopicReport::factory()
        ->for(Topic::factory()->for(BidderRound::factory()))
        ->create();
    $amountFormatted = '12,4 €';
    $reminder = new BidderRoundFound($report, $amountFormatted, $roundWon);
    $mailMessage = $reminder->toMail();
    expect($mailMessage->introLines)->toContain("Es ist soweit! Für das Produkt $report->name steht die Runde fest.")
        ->and($mailMessage->introLines)->toContain("Damit liegt dein monatlicher Beitrag bei $amountFormatted")
        ->and($mailMessage->introLines)->toContain("Die Runde $roundWon reicht aus!");
});
