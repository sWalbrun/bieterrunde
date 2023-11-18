<?php

namespace App\BidderRound;

use App\Enums\EnumTargetAmountReachedStatus;
use App\Exceptions\NoRoundFoundException;
use App\Models\Topic;
use App\Models\TopicReport;

class TargetAmountReachedReport
{
    public function __construct(
        /**
         * This property should be actually readonly but mockery does not support the initialization with parameters
         */
        public EnumTargetAmountReachedStatus $status,
        private readonly Topic $topic,
        private readonly ?TopicReport $topicReport,
    ) {
    }

    /**
     * @throws NoRoundFoundException
     */
    public function roundWon(): int
    {
        if (! $this->status->isReportAvailable()) {
            throw new NoRoundFoundException("No round has been found for topic ($this->topic)");
        }

        return $this->topicReport->roundWon;
    }

    /**
     * @throws NoRoundFoundException
     */
    public function sumAmountFormatted(): string
    {
        if (! $this->status->isReportAvailable()) {
            throw new NoRoundFoundException("No round has been found for bidder round ($this->topic)");
        }

        return $this->topicReport->sumAmountFormatted;
    }
}
