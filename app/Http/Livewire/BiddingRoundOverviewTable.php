<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
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
    use BiddingRoundOverviewUpdate;

    public bool $showUpdateMessages = true;

    public ?int $bidderRoundId;

    private ?BidderRound $bidderRound;

    public const USER_ID = User::COL_ID;

    public function setUp(): array
    {
        $this->showCheckBox()
            ->showSearchInput()
            ->showRecordCount()
            ->showPerPage()
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
            ->addColumn(self::USER_ID, fn(User $user) => $user->id)
            ->addColumn(User::COL_EMAIL, fn(User $user) => $user->email)
            ->addColumn(User::COL_NAME, fn(User $user) => $user->name)
            ->addColumn(User::COL_CONTRIBUTION_GROUP, fn(User $user) => trans()->get($user->contributionGroup))
            ->addColumn(User::COL_COUNT_SHARES, fn(User $user) => $user->countShares)
            ->addColumn(User::COL_PAYMENT_INTERVAL, fn(User $user) => trans()->get($user->paymentInterval ?? trans('nicht gegeben')));

        $this->bidderRound ??= BidderRound::query()->find($this->bidderRoundId);

        for ($round = 1; $round <= $this->bidderRound->countOffers; $round++) {
            $columns->addColumn(
                $this->getRoundIdentifier($round),
                fn(User $user) => $user
                    ->offers
                    ->filter(fn(Offer $offer) => $offer->round === $round)
                    ->map(fn(Offer $offer) => $offer->amount)
                    ->first(null, '0')
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
            Column::add()
                ->title(trans('laravelusers::laravelusers.show-user.contributionGroup'))
                ->field(User::COL_CONTRIBUTION_GROUP)
                ->searchable()
                ->sortable(),
            Column::add()
                ->title(trans('laravelusers::laravelusers.show-user.countShares'))
                ->field(User::COL_COUNT_SHARES),
            Column::add()
                ->title(trans('Zahlungsintervall'))
                ->field(User::COL_PAYMENT_INTERVAL)
                ->searchable()
                ->sortable()
                ->editOnClick(),
        ];

        if (!$this->isBidderRoundGiven()) {
            return $columns;
        }

        $this->bidderRound ??= BidderRound::find($this->bidderRoundId);
        $noReportExisting = $this->bidderRound->bidderRoundReport()->doesntExist();

        for ($round = 1; $round <= $this->bidderRound->countOffers; $round++) {
            $columns[] = Column::add()
                ->title(trans('Runde :round', ['round' => $round]))
                ->field($this->getRoundIdentifier($round))
                ->editOnClick($noReportExisting);
        }

        return $columns;
    }

    /**
     * @return bool
     */
    private function isBidderRoundGiven(): bool
    {
        return isset($this->bidderRoundId) && $this->bidderRoundId > 0;
    }

    private function getRoundIdentifier(int $round): string
    {
        return "round$round";
    }

    private function getRoundFromIdentifier(string $roundIdentifier): int
    {
        return intval(substr($roundIdentifier, strlen('round')));
    }

    private function updateRound(array $data): bool
    {
        $this->bidderRound ??= BidderRound::find($this->bidderRoundId);

        // phpcs:ignore
        //** @var User $user */
        $user = User::query()->findOrFail($data[self::USER_ID]);

        // phpcs:ignore
        //** @var Offer $offer */
        $offer = $user->offersForRound($this->bidderRound)
            ->get()
            ->first(fn(Offer $offer) => $this->getRoundIdentifier($offer->round) === $data['field']);

        if (!isset($offer)) {
            $offer = new Offer();
        }

        $amount = $data['value'];
        if (Validator::make(
            ['amount' => $amount],
            ['amount' => OfferForm::OFFER_AMOUNT_VALIDATION]
        )->fails()
        ) {
            return false;
        }

        $offer->amount = $amount;

        if (!$offer->exists) {
            $offer->save();
            $offer->round = $this->getRoundFromIdentifier($data['field']);
            $offer->bidderRound()->associate($this->bidderRound);
            $offer->user()->associate($user);
        }
        $offer->save();

        return true;
    }
}
