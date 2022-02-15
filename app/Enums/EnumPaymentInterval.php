<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;

/**
 * @method static static ANNUAL()
 * @method static static SEMIANNUAL()
 * @method static static QUARTERLY()
 */
final class EnumPaymentInterval extends Enum
{
    public const ANNUAL = 'ANNUAL';
    public const SEMIANNUAL = 'SEMIANNUAL';
    public const MONTHLY = 'MONTHLY';

    /**
     * This method tries to determine the interval by comparing with the key, the value or even the translation for a match.
     *
     * @param string $interval
     *
     * @return self|null
     *
     * @throws InvalidEnumKeyException
     */
    public static function determine(string $interval): ?self
    {
        if (self::hasValue($interval)) {
            return self::fromValue($interval);
        }

        if (self::hasValue($interval)) {
            return self::fromKey($interval);
        }

        $foundInterval = collect(self::getValues())
            ->filter(fn (string $value) => trans($value) === $interval)
            ->map(fn (string $value) => self::fromValue($value));
        if ($foundInterval->isEmpty()) {
            throw new InvalidEnumKeyException($interval, self::class);
        }

        return $foundInterval
            ->first();
    }
}
