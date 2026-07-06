<?php

namespace App\Exports;

use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * The fixed monthly contributions per member of a finished topic —
 * meant for bookkeeping once the {@link Topic::$topicReport report} exists.
 */
class FinalAmountsExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Topic $topic) {}

    public function headings(): array
    {
        return [
            trans('Name'),
            trans('E-Mail'),
            trans('Count shares'),
            trans('Amount per share'),
            trans('Monthly amount'),
            trans('Payment interval'),
        ];
    }

    public function collection(): Collection
    {
        $roundWon = $this->topic->topicReport->roundWon;

        return $this->topic
            ->shares()
            ->with('user')
            ->get()
            ->map(function (Share $share) use ($roundWon) {
                /** @var Offer|null $offer */
                $offer = $share->user
                    ->offersForTopic($this->topic)
                    ->where(Offer::COL_ROUND, '=', $roundWon)
                    ->first();
                $multiplier = $share->calculableValue();

                return [
                    $share->user->name,
                    $share->user->email,
                    $multiplier,
                    $offer?->amount,
                    isset($offer) ? round($offer->amount * $multiplier, 2) : null,
                    isset($share->user->paymentInterval) ? trans($share->user->paymentInterval->value) : null,
                ];
            });
    }
}
