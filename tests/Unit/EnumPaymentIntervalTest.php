<?php

namespace Tests\Unit;

use App\BidderRound\Participant;
use App\Enums\EnumPaymentInterval;
use App\Jobs\RememberTheBidderRound;
use App\Models\BidderRound;
use App\Models\User;
use App\Notifications\ReminderOfBidderRound;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Translation\Translator;
use Tests\TestCase;

class EnumPaymentIntervalTest extends TestCase
{
    /**
     * This test makes sure the determination is working fine.
     *
     * @dataProvider validAndInvalidIntervals()
     *
     * @param bool $throws
     * @param array ...$intervals
     *
     * @return void
     *
     * @throws InvalidEnumKeyException
     */
    public function testDetermine(bool $throws, ...$intervals)
    {
        if ($throws) {
            $this->expectException(InvalidEnumKeyException::class);
        }

        collect($intervals)->each(fn (string $interval) => $this->assertInstanceOf(
            EnumPaymentInterval::class,
            EnumPaymentInterval::determine($interval)
        ));
    }

    public function validAndInvalidIntervals(): array
    {
        return [
            'all matching' => [
                false,
                EnumPaymentInterval::ANNUAL()->value,
                EnumPaymentInterval::ANNUAL()->key,
            ],
            'none matching' => [
                true,
                'Kleines Bubu',
                'Electronic Circus',
            ]
        ];
    }
}
