<?php

namespace App\Http\Livewire;

use App\Enums\EnumPaymentInterval;
use App\Models\BidderRound;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nette\NotImplementedException;

/**
 * This class is a table which is showing all offers of all user for one {@link BidderRound}.
 */
trait BiddingRoundOverviewUpdate
{
    private ?BidderRound $bidderRound;

    public function update(array $data): bool
    {
        if (!isset($data[self::USER_ID])
            || !isset($data['value'])
            || !isset($data['field'])
        ) {
            Log::error('Setting the offer via overview table did not work out for data (' . json_encode($data) . ')');

            return false;
        }

        if (Str::startsWith($data['field'], 'round')) {
            return $this->updateRound($data);
        }

        if ($data['field'] === User::COL_PAYMENT_INTERVAL) {
            $user = User::query()->findOrFail($data[self::USER_ID]);
            $user->paymentInterval = EnumPaymentInterval::hasValue($data['value']) ? $data['value'] : null;
            $user->save();

            return true;
        }

        Log::info('No update logic found for data (' . json_encode($data) . ')');

        return false;
    }

    public function updateMessages(string $status, string $field = '_default_message')
    {
        switch ($status) {
            case 'success':
                return trans('Änderung wurde gespeichert');

            case 'error':
                return trans('Das Ändern der Runde war nicht erfolgreich');

            default:
                throw new NotImplementedException("No implementation for status ($status) found");
        }
    }
}
