<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridEloquent;
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;

/**
 * This class is a table which is showing all offers of all user for one {@link BidderRound}.
 */
final class BiddingRoundOverviewTable extends PowerGridComponent
{
    use ActionButton;

    public bool $showUpdateMessages = true;

    public ?int $bidderRoundId;
    private ?BidderRound $bidderRound;

    public function setUp(): void
    {
        $this->showCheckBox()
            ->showSearchInput()
            ->showExportOption('download', ['excel', 'csv']);
    }

    public function datasource(): Builder
    {
        if (!$this->isBidderRoundGiven()) {
            return User::bidderRoundParticipants();
        }

        return User::bidderRoundWithRelations($this->bidderRoundId);
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

    /**
     * The return value will be used for adding the data rows.
     *
     * @return PowerGridEloquent|null
     */
    public function addColumns(): ?PowerGridEloquent
    {
        if (!isset($this->bidderRoundId) || $this->bidderRoundId <= 0) {
            return null;
        }

        $columns = PowerGrid::eloquent()
            ->addColumn(User::COL_ID, fn (User $user) => $user->id)
            ->addColumn(User::COL_EMAIL, fn (User $user) => $user->email)
            ->addColumn(User::COL_NAME, fn (User $user) => $user->name);

        /** @var BidderRound $bidderRound */
        $this->bidderRound ??= BidderRound::query()->find($this->bidderRoundId);

        for ($round = 1; $round <= $this->bidderRound->countOffers; $round++) {
            $columns->addColumn(
                $this->getRoundIdentifier($round),
                fn (User $user) => $user
                    ->offers
                    ->filter(fn (Offer $offer) => $offer->round === $round)
                    ->map(fn (Offer $offer) => $offer->amount)
                    ->first()
            );
        }

        return $columns;
    }

    /**
     * The return value will be used for the heading row.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        $columns = [
            Column::add()
                ->title(trans('E-Mail'))
                ->field(User::COL_EMAIL)
                ->searchable()
                ->sortable(),
            Column::add()
                ->title(trans('Name'))
                ->field(User::COL_NAME)
                ->searchable()
                ->sortable(),
        ];

        if (!$this->isBidderRoundGiven()) {
            return $columns;
        }

        /** @var BidderRound $bidderRound */
        $this->bidderRound ??= BidderRound::find($this->bidderRoundId);

        for ($round = 1; $round <= $this->bidderRound->countOffers; $round++) {
            $columns[] = Column::add()
                ->title(trans('Runde :round', ['round' => $round]))
                ->field($this->getRoundIdentifier($round))
                ->withSum(trans('Summe'), false, true);
        }

        return $columns;
    }

    /**
     * @return bool
     */
    private function isBidderRoundGiven(): bool
    {
        return isset($this->bidderRoundId) && $this->bidderRoundId >= 0;
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
