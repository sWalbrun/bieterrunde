<?php

namespace App\BidderRound;

use App\Exceptions\NoRoundFoundException;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;

class TargetAmountReachedReport
{
    public function __construct(
        /**
         * This property should be actually readonly but mockery does not support the initialization with parameters
         */
        public EnumTargetAmountReachedStatus $status,
        private readonly BidderRound                  $bidderRound,
        private readonly BidderRoundReport|null       $bidderRoundReport,
    )
    {
    }

    /**
     * @throws NoRoundFoundException
     */
    public function roundWon(): int
    {
        if (!$this->status->isReportAvailable()) {
            throw new NoRoundFoundException("No round has been found for bidder round ($this->bidderRound)");
        }
        return $this->bidderRoundReport->roundWon;
    }

    /**
     * @throws NoRoundFoundException
     */
    public function sumAmountFormatted(): string
    {
        if (!$this->status->isReportAvailable()) {
            throw new NoRoundFoundException("No round has been found for bidder round ($this->bidderRound)");
        }
        return $this->bidderRoundReport->sumAmountFormatted;
    }
}
