<?php

namespace Tests\Unit\Enums;

use App\Enums\EnumPaymentInterval;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Tests\TestCase;

class EnumPaymentIntervalTest extends TestCase
{
    /**
     * This test makes sure the determination is working fine.
     *
     * @dataProvider validAndInvalidIntervals()
     *
     * @param  array  ...$intervals
     * @return void
     *
     * @throws InvalidEnumKeyException
     */
    public function test_determine(bool $throws, ...$intervals)
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
            ],
        ];
    }
}
