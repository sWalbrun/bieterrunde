<?php

use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Notifications\BidderRoundFound;

it('builds a fully qualified mail', function () {
    $roundWon = 1;
    /** @var BidderRoundReport $report */
    $report = BidderRoundReport::factory()
        ->for(BidderRound::factory())
        ->create();
    $amountFormatted = '12,4 €';
    $reminder = new BidderRoundFound($report, $amountFormatted, $roundWon);
    $mailMessage = $reminder->toMail();
    expect($mailMessage->introLines)->toContain("Es ist soweit! Die Runde $roundWon wurde als ausreichende Runde ermittelt!")
        ->and($mailMessage->introLines)->toContain("Damit liegt dein monatlicher Beitrag bei $amountFormatted");
});
