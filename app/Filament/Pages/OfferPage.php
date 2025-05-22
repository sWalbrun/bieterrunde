<?php

namespace App\Filament\Pages;

use App\BidderRound\TopicService;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Enums\ShareValue;
use App\Filament\EnumNavigationGroups;
use App\Filament\Utils\ForFilamentTranslator;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Members can submit their bids via this page. Bids can only be submitted
 * within the {@link BidderRound::$startOfSubmission configured time period}.
 */
class OfferPage extends Page implements HasForms
{
    use HasPageShield;

    private const USER = 'user';

    public const ROUND_TO_TOTAL_AMOUNT_MAPPING = 'roundToTotalAmountMapping';

    public const ROUND_TO_PARTIAL_AMOUNT_MAPPING = 'roundToPartialAmountMapping';

    public const USER_CONTRIBUTION_GROUP = 'userContributionGroup';

    public const USER_PAYMENT_INTERVAL = 'userPaymentInterval';

    public const PERMISSION_NAME = 'page_OfferPage';

    /**
     * @var Collection<string, int[]>
     */
    public Collection $roundToTotalAmountMapping;

    /**
     * @var array<string, int[]>
     */
    public array $roundToPartialAmountMapping = [];

    /**
     * @var Collection<string, string> Holds the share keys for the topics
     */
    public Collection $topicToShareMapping;

    public ?User $user = null;

    public EnumPaymentInterval|string|null $userPaymentInterval = null;

    public EnumContributionGroup|string|null $userContributionGroup = null;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static string $view = 'filament.pages.offer-page';

    public function __construct()
    {
        $this->roundToTotalAmountMapping = collect();
        $this->topicToShareMapping = collect();
    }

    public static function url(): string
    {
        return url('/main/offer-page');
    }

    protected static function getPermissionName(): string
    {
        return static::PERMISSION_NAME;
    }

    public function getHeading(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::YOUR_OFFERS);
    }

    public function getFormModel(): ?BidderRound
    {
        return BidderRound::query()
            ->started()
            ->latest(BidderRound::COL_START_OF_SUBMISSION)
            ->first();
    }

    private function formatAmount(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function putShareCount(Topic $topic): ?ShareValue
    {
        /** @var Share|null $share */
        $share = $topic
            ->shares
            ->first(fn (Share $share) => $share->fkUser === $this->user->id);
        $shareValue = $share?->value;
        $this->topicToShareMapping->put(
            $topic->id,
            $shareValue?->key
        );

        return $shareValue;
    }

    private function putPartialAmount(int $key, int $numberOfRound, float $amount): void
    {
        $partialAmounts = $this->roundToPartialAmountMapping[$key] ?? [];
        $partialAmounts += [$numberOfRound => $this->formatAmount($amount)];
        $this->roundToPartialAmountMapping[$key] = $partialAmounts;
    }

    private function putTotalAmount(int $key, int $numberOfRound, ?float $amount): void
    {
        $totalAmounts = $this->roundToTotalAmountMapping->get($key) ?? [];
        $totalAmounts += [$numberOfRound => $amount ?? null];
        $this->roundToTotalAmountMapping->put($key, $totalAmounts);
    }

    public static function getNavigationLabel(): string
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
                ->requiresConfirmation()
                ->modalHeading(trans('Make an offer'))
                ->modalSubheading(trans('Subscribe now with obligation to pay'))
                ->action(fn () => $this->save()),
        ];
    }

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->userContributionGroup = isset($this->user->contributionGroup) ? trans($this->user->contributionGroup->value) : null;
        $this->userPaymentInterval = $this->user->paymentInterval?->value;
    }

    protected function getFormSchema(): array
    {
        $record = $this->getFormModel();
        if (! isset($record)) {
            return [];
        }

        $topicsOfInterest = $this->getFormModel()?->topics()->whereHas(
            'shares',
            fn (Builder $query) => $query->where(Share::COL_FK_USER, '=', $this->user->id)
        );
        $fieldSets = $topicsOfInterest->chunkMap(function (Topic $topic) {
            $buildOffer = function (?Offer $offer, int $numberOfRound) use ($topic) {
                $shareValue = $this->putShareCount($topic);
                $amount = $offer?->amount;
                if (isset($shareValue)) {
                    $this->putTotalAmount($topic->id, $numberOfRound, $amount ? $amount * $shareValue->calculable() : null);
                    if (isset($amount)) {
                        $this->putPartialAmount($topic->id, $numberOfRound, $amount);
                    }
                }

                return [TextInput::make("roundToTotalAmountMapping.$topic->id.$numberOfRound")
                    ->label(trans('Total :numberOfRound offer', ['numberOfRound' => $numberOfRound]))
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(
                        fn ($state, $set) => $set(
                            "roundToPartialAmountMapping.$topic->id.$numberOfRound",
                            $this->formatAmount($state / ShareValue::fromKey($this->topicToShareMapping->get($topic->id))->calculable())
                        )
                    )
                    ->mask(
                        <<<'JS'
                IMask($el, {
                    mask: Number,
                    scale: 2,
                    signed: false,
                    thousandsSeparator: '.',
                    padFractionalZeros: true,
                    normalizeZeros: true,
                    radix: ',',
                    mapToRadix: [','],
                    min: 1,
                    max: 200,
                });
                JS
                    )
                    ->hint(
                        $offer?->isOfWinningRound()
                            ? trans('Round with enough turnover')
                            : null
                    )->hintColor('success')
                    ->suffix('€')
                    ->required(),
                    TextInput::make("roundToPartialAmountMapping.$topic->id.$numberOfRound")
                        ->label(trans('Partial :numberOfRound offer', ['numberOfRound' => $numberOfRound]))
                        ->disabled()
                        ->suffix('€')];
            };

            return Fieldset::make($topic->name)
                ->schema(
                    [
                        Select::make('topicToShareMapping.'.$topic->id)
                            ->label(trans('Count shares'))
                            ->options(ForFilamentTranslator::enum(ShareValue::getInstances()))
                            ->disabled(),
                        Card::make(
                            TopicService::getOffers($topic, $this->user)
                                ->map($buildOffer)
                                ->flatten(1)
                                ->toArray()
                        )
                            ->columns(2)
                            ->disabled(fn () => ! $topic->isOfferStillPossible()),
                    ]);
        });

        return [
            // We have to make a workaround for this value since the contribution group is a
            // bensampo enum and the arrayble casts (Enum::toArray()) is making problems combined
            // with filament
            Card::make([
                TextInput::make(self::USER.'.'.User::COL_NAME)->disabled(),
                TextInput::make(self::USER.'.'.User::COL_EMAIL)->disabled(),
                TextInput::make(self::USER_CONTRIBUTION_GROUP)
                    ->label(trans('Contribution group'))
                    ->disabled(),
                Select::make(self::USER_PAYMENT_INTERVAL)
                    ->label(trans('Payment interval'))
                    ->options(
                        ForFilamentTranslator::enum(EnumPaymentInterval::getInstances())
                    )
                    ->required(),
            ]),
            ...$fieldSets,
        ];
    }

    public function save(): void
    {
        $this->validate();
        $atLeastOneChange = false;
        collect($this->roundToPartialAmountMapping)->each(function (array $rounds, $topicId) use (&$atLeastOneChange) {
            collect($rounds)->each(function ($amountOfRound, $numberOfRound) use ($topicId, &$atLeastOneChange) {
                /** @var Topic $topic */
                $topic = Topic::query()->findOrFail($topicId);
                /** @var Offer $offer */
                $offer = $this
                    ->user
                    ->offers()
                    ->where(Offer::COL_ROUND, '=', $numberOfRound)
                    ->where(Offer::COL_FK_TOPIC, '=', $topic->id)
                    ->first() ?? new Offer;
                $offer->round = $numberOfRound;
                $offer->amount = floatval($amountOfRound);
                $offer->topic()->associate($topicId);
                $offer->user()->associate($this->user);
                $atLeastOneChange |= $offer->isDirty();
                $offer->save();
            });
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
