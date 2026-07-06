<?php

namespace App\BidderRound;

use App\Enums\EnumPaymentInterval;
use App\Models\Offer;
use App\Models\Topic;
use App\Models\User;

/**
 * Handles the write path of the user's offers (used by the livewire offer form).
 */
class OfferService
{
    /**
     * Upserts the given offers. The amounts are expected PER SINGLE SHARE
     * (that is the value which gets persisted on {@link Offer::$amount}).
     * Topics for which offers are no longer possible are skipped.
     *
     * @param  array<int|string, array<int|string, float|null>>  $perShareAmountsByTopic  [topicId => [round => amountPerShare]]
     * @return bool true if at least one offer or the payment interval changed
     */
    public function saveOffers(
        User $user,
        array $perShareAmountsByTopic,
        EnumPaymentInterval|string|null $paymentInterval = null
    ): bool {
        $atLeastOneChange = false;

        foreach ($perShareAmountsByTopic as $topicId => $rounds) {
            /** @var Topic $topic */
            $topic = Topic::query()->findOrFail($topicId);
            if (! $topic->isOfferStillPossible()) {
                continue;
            }

            foreach ($rounds as $round => $amount) {
                if (! isset($amount)) {
                    continue;
                }

                /** @var Offer $offer */
                $offer = $user
                    ->offers()
                    ->where(Offer::COL_ROUND, '=', $round)
                    ->where(Offer::COL_FK_TOPIC, '=', $topic->id)
                    ->first() ?? new Offer;
                $offer->round = (int) $round;
                $offer->amount = (float) $amount;
                $offer->topic()->associate($topic);
                $offer->user()->associate($user);
                $atLeastOneChange = $offer->isDirty() || $atLeastOneChange;
                $offer->save();
            }
        }

        if (isset($paymentInterval)) {
            $atLeastOneChange = $atLeastOneChange
                || ! $user->paymentInterval
                || $user->paymentInterval->isNot($paymentInterval);
            $user->paymentInterval = $paymentInterval;
            $user->save();
        }

        return $atLeastOneChange;
    }

    /**
     * Parses a user given amount in german notation ("1.234,56" → 1234.56).
     * Plain english decimals ("52.5") keep working. This replaces the former
     * floatval() call which silently truncated "1.234,56" to 1.234.
     */
    public static function parseGermanAmount(string|float|int|null $value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $value = trim(str_replace(['€', ' '], '', $value ?? ''));
        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            // German notation: dots are thousand separators, comma is the decimal separator
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            // Pure thousands notation without decimals ("1.234")
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
