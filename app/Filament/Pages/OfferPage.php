<?php

namespace App\Filament\Pages;

use App\BidderRound\BidderRoundService;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Filament\EnumNavigationGroups;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Actions\Concerns\HasForm;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * Members can submit their bids via this page. Bids can only be submitted
 * within the {@link BidderRound::$startOfSubmission configured time period}.
 */
class OfferPage extends Page
{
    use HasForm;
    use HasPageShield;

    private const USER = 'user';

    public const ROUND_TO_AMOUNT_MAPPING = 'roundToAmountMapping';

    public const USER_CONTRIBUTION_GROUP = 'userContributionGroup';

    public const USER_PAYMENT_INTERVAL = 'userPaymentInterval';

    /**
     * @var Collection<int, float> round to amount mapping
     */
    public Collection $roundToAmountMapping;

    /**
     * @var Collection<int, Offer> round to amount mapping
     */
    public Collection $roundToOfferMapping;

    public User|null $user = null;

    public EnumPaymentInterval|string|null $userPaymentInterval = null;

    public EnumContributionGroup|string|null $userContributionGroup = null;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static string $view = 'filament.pages.offer-page';

    public static function url(): string
    {
        return url('/main/offer-page');
    }

    public function getHeading(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::YOUR_OFFERS);
    }

    public function getFormModel(): BidderRound|null
    {
        return BidderRound::query()
            ->started()
            ->latest(BidderRound::COL_START_OF_SUBMISSION)
            ->first();
    }

    protected static function getNavigationLabel(): string
    {
        return BidderRound::query()
            ->started()
            ->latest(BidderRound::COL_START_OF_SUBMISSION)
            ->first()
            ?->__toString() ?? trans('Bidder round');
    }

    protected function getActions(): array
    {
        return [
            Action::make('Save')
                ->translateLabel()
                ->disabled(! $this->getFormModel()?->isOfferStillPossible())
                ->action(fn () => $this->save()),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        $this->user = auth()->user();
        $this->roundToOfferMapping = BidderRoundService::getOffers($this->getFormModel(), $this->user)
            // The dehydration of filament cannot handle null values within a collection with models. Therefore, we insert
            // empty offers instead.
            ->map(fn (Offer|null $offer) => $offer ?? new Offer());
        $this->roundToAmountMapping = $this->roundToOfferMapping->map(fn (Offer|null $offer) => $offer?->amount);
        $this->formData([
            self::USER => $this->user,
            self::USER_CONTRIBUTION_GROUP => isset($this->user->contributionGroup) ? trans($this->user->contributionGroup->value) : null,
            self::USER_PAYMENT_INTERVAL => $this->user->paymentInterval?->value,
        ]);
    }

    protected function getFormSchema(): array
    {
        $record = $this->getFormModel();
        if (! isset($record)) {
            return [];
        }

        return [
            // We have to make a workaround for this value since the contribution group is a
            // bensampo enum and the arrayble casts (Enum::toArray()) is making problems combined
            // with filament
            Card::make([
                TextInput::make(self::USER.'.'.User::COL_NAME),
                TextInput::make(self::USER.'.'.User::COL_EMAIL),
                TextInput::make(self::USER_CONTRIBUTION_GROUP)
                    ->label(trans('Contribution group')),
                TextInput::make(self::USER.'.'.User::COL_COUNT_SHARES)
                    ->label(trans('Count shares')),
            ])->disabled(),
            Card::make([
                Select::make(self::USER_PAYMENT_INTERVAL)
                    ->label(trans('Payment interval'))
                    ->options(
                        collect(EnumPaymentInterval::getInstances())
                            ->mapWithKeys(fn (EnumPaymentInterval $value) => [$value->key => trans($value->value)])
                    )
                    ->required(),
                ...collect($this->roundToAmountMapping)->map(
                    // See the mount method for setting the corresponding values
                    fn (string|null $amountOfRound, $numberOfRound) => TextInput::make(self::ROUND_TO_AMOUNT_MAPPING.".$numberOfRound")
                        ->label(trans('Offer :numberOfRound', ['numberOfRound' => $numberOfRound]))
                        ->numeric()
                        ->mask(
                            fn (TextInput\Mask $mask) => $mask
                                ->numeric()
                                ->decimalPlaces(2)
                                ->decimalSeparator(',')
                                ->minValue(1)
                                ->maxValue(200)
                                ->normalizeZeros()
                                ->padFractionalZeros()
                                ->thousandsSeparator('.')
                        )
                        ->hint(
                            $this->roundToOfferMapping->get($numberOfRound)?->isOfWinningRound()
                                ? trans('Round with enough turnover')
                                : null
                        )->hintColor('success')
                        ->suffix('€')
                        ->required()
                )->toArray(),
            ])->disabled(! $this->getFormModel()?->isOfferStillPossible()),
        ];
    }

    public function save(): void
    {
        $this->validate();
        $atLeastOneChange = false;
        collect($this->roundToAmountMapping)->each(function (string|null $amountOfRound, $numberOfRound) use (&$atLeastOneChange) {
            // phpcs:ignore
            /** @var Offer $offer */
            $offer = $this->user->offers()->where(Offer::COL_ROUND, '=', $numberOfRound)->first() ?? new Offer();
            $offer->round = $numberOfRound;
            $offer->amount = $amountOfRound;
            $offer->bidderRound()->associate($this->getFormModel());
            $offer->user()->associate($this->user);
            $atLeastOneChange |= $offer->isDirty();
            $offer->save();
        })->toArray();

        $atLeastOneChange |= $this->user->paymentInterval?->isNot($this->userPaymentInterval);
        $this->user->paymentInterval = $this->userPaymentInterval;
        $this->user->save();

        if ($atLeastOneChange) {
            Notification::make()
                ->title(trans('Vielen Dank für deine Gebote. Sobald es Neuigkeiten gibt, melden wir uns!'))
                ->success()
                ->send();
        }
    }
}
