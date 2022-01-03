<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridEloquent;
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;

/**
 * This class is a table which is showing all offers of all user for one {@link BidderRound}
 */
final class BiddingRoundOverviewTable extends PowerGridComponent
{
    use ActionButton;

    public bool $showUpdateMessages = true;

    public ?int $bidderRoundId;

    private Collection $dataSource;

    public function setUp(): void
    {
        $this->showCheckBox()
            ->showPerPage()
            ->showSearchInput()
            ->showExportOption('download', ['excel', 'csv']);
    }

    public function datasource(): Collection
    {
        if ($this->isBidderRoundGiven()) {
            return collect();
        }
        $offers = Offer::getOffersForBidderRound($this->bidderRoundId);

        $dataSource = collect();
        $offers->each(function (Offer $offer) use ($dataSource) {
            if ($dataSource->first(fn (Collection $entry) => $entry->has(User::COL_EMAIL)) === null) {
                $entry = collect([
                    User::COL_ID => $offer->user->id,
                    User::COL_EMAIL => $offer->user->email,
                    User::COL_NAME => $offer->user->name
                ]);
                $dataSource->push($entry);
            }
            if (!isset($entry)) {
                /** @var Collection $entry */
                $entry = $dataSource
                    ->first(fn (Collection $entry) => $entry->has(User::COL_EMAIL)
                        && $entry->get(User::COL_EMAIL) === $offer->user->email);
            }

            $entry->put($this->getRoundIdentifier($offer->round), $offer->amount);
        });
        $this->dataSource = $dataSource;
        return $dataSource;
    }

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): ?PowerGridEloquent
    {
        $columns = PowerGrid::eloquent()
            ->addColumn(User::COL_ID, fn (Collection $offer) => $offer->get(User::COL_ID))
            ->addColumn(User::COL_EMAIL, fn (Collection $offer) => $offer->get(User::COL_EMAIL))
            ->addColumn(User::COL_NAME, fn (Collection $offer) => $offer->get(User::COL_NAME));
        $firstRow = $this->dataSource->first();

        if (!($firstRow instanceof Collection)) {
            return null;
        }
        $rounds = $firstRow->filter(fn ($value, string $key) => !in_array(
            $key,
            [
                User::COL_ID,
                User::COL_EMAIL,
                User::COL_NAME
            ]
        ));
        $rounds->each(
            fn ($amount, string $round) => $columns->addColumn($round, fn (Collection $offer) => $offer->get($round))
        );
        return $columns;
    }

    /**
     * PowerGrid Columns.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        $columns = [
            Column::add()
                ->title(trans("E-Mail"))
                ->field(User::COL_EMAIL)
                ->searchable()
                ->sortable(),
            Column::add()
                ->title(trans("Name"))
                ->field(User::COL_NAME)
                ->searchable()
                ->sortable(),
        ];

        if (!isset($this->bidderRoundId) || $this->bidderRoundId <= 0) {
            return $columns;
        }

        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::find($this->bidderRoundId);

        for ($round = 1; $round <= $bidderRound->countOffers; $round++) {
            $columns[] = Column::add()
                ->title(trans('Runde :round', ['round' => $round]))
                ->field($this->getRoundIdentifier($round));
        }
        return $columns;
    }

    /**
     * @return bool
     */
    private function isBidderRoundGiven(): bool
    {
        return !isset($this->bidderRoundId) || $this->bidderRoundId <= 0;
    }

    /**
     * @param int $round
     * @return string
     */
    private function getRoundIdentifier(int $round): string
    {
        return "round$round";
    }
}
