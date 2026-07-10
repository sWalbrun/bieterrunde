<?php

namespace App\Livewire;

use App\BidderRound\OfferService;
use App\BidderRound\TopicService;
use App\Enums\EnumPaymentInterval;
use App\Models\BidderRound;
use App\Models\BidderRoundComment;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\OfferReceipt;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The heart of the user area: members submit their monthly offers per topic
 * and round of the currently running {@link BidderRound}.
 *
 * The member enters the TOTAL monthly amount (covering all of their shares);
 * persisted is the amount per single share (see {@link OfferService}).
 */
class OfferForm extends Component
{
    #[Locked]
    public ?int $roundId = null;

    #[Locked]
    public ?string $roundName = null;

    #[Locked]
    public ?string $roundEnd = null;

    /**
     * View models of the topics the member holds shares of:
     * [topicId => [id, name, multiplier, shareLabel, editable, winningRound, rounds]]
     */
    #[Locked]
    public array $topics = [];

    /**
     * The user facing total amounts: [topicId => [round => string|null]]
     */
    public array $amounts = [];

    public ?string $paymentInterval = null;

    /** Free-text feedback for the round (github issue #12). */
    public ?string $comment = null;

    public bool $saved = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->paymentInterval = $user->paymentInterval?->value;

        /** @var BidderRound|null $round */
        $round = BidderRound::query()
            ->started()
            ->latest(BidderRound::COL_START_OF_SUBMISSION)
            ->first();
        if (! isset($round)) {
            return;
        }
        $this->roundId = $round->id;
        $this->roundName = (string) $round;
        $this->roundEnd = $round->endOfSubmission->format('d.m.Y');
        $this->comment = $round->comments()
            ->where(BidderRoundComment::COL_FK_USER, '=', $user->id)
            ->value(BidderRoundComment::COL_COMMENT);

        $round
            ->topics()
            ->whereHas('shares', fn (Builder $query) => $query->where(Share::COL_FK_USER, '=', $user->id))
            ->get()
            ->each(function (Topic $topic) use ($user) {
                /** @var Share|null $share */
                $share = $topic->sharesForUser($user)->first();
                $multiplier = $share?->value->calculable() ?? 1.0;

                $this->topics[$topic->id] = [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'multiplier' => $multiplier,
                    'shareLabel' => self::formatNumber($multiplier),
                    'editable' => $topic->isOfferStillPossible(),
                    'winningRound' => $topic->topicReport?->roundWon,
                    'rounds' => range(1, $topic->rounds),
                ];

                TopicService::getOffers($topic, $user)->each(function (?Offer $offer, int $round) use ($topic, $multiplier) {
                    $this->amounts[$topic->id][$round] = isset($offer)
                        ? self::formatNumber($offer->amount * $multiplier)
                        : null;
                });
            });
    }

    public function save(): void
    {
        $this->validate();

        /** @var User $user */
        $user = auth()->user();

        $perShareAmountsByTopic = [];
        foreach ($this->topics as $topicId => $topic) {
            if (! $topic['editable']) {
                continue;
            }
            foreach ($this->amounts[$topicId] ?? [] as $round => $value) {
                $amount = OfferService::parseGermanAmount($value);
                if (! isset($amount)) {
                    continue;
                }
                $perShareAmountsByTopic[$topicId][$round] = round($amount / $topic['multiplier'], 2);
            }
        }

        $offerService = app(OfferService::class);
        $changed = $offerService->saveOffers($user, $perShareAmountsByTopic, $this->paymentInterval);

        /** @var BidderRound $round */
        $round = BidderRound::query()->findOrFail($this->roundId);
        $offerService->saveComment($user, $round, $this->comment);

        if ($changed) {
            $user->notify(new OfferReceipt($this->offerSummary(), $this->roundName ?? trans('Bidder round')));
        }
        $this->saved = true;
    }

    /**
     * The submitted totals per topic, formatted for the receipt mail.
     *
     * @return array<string, array<int|string, string>>
     */
    private function offerSummary(): array
    {
        $summary = [];
        foreach ($this->topics as $topicId => $topic) {
            if (! $topic['editable']) {
                continue;
            }
            foreach ($this->amounts[$topicId] ?? [] as $round => $value) {
                $amount = OfferService::parseGermanAmount($value);
                if (isset($amount)) {
                    $summary[$topic['name']][$round] = number_format($amount, 2, ',', '.');
                }
            }
        }

        return $summary;
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('livewire.offer-form', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'userContributionGroup' => isset($user->contributionGroup) ? trans($user->contributionGroup->value) : null,
            'paymentIntervals' => EnumPaymentInterval::getInstances(),
            'anyEditable' => collect($this->topics)->contains(fn (array $topic) => $topic['editable']),
        ]);
    }

    protected function rules(): array
    {
        $rules = [
            'paymentInterval' => ['required', Rule::in(EnumPaymentInterval::getValues())],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
        foreach ($this->topics as $topicId => $topic) {
            if (! $topic['editable']) {
                continue;
            }
            foreach ($topic['rounds'] as $round) {
                $rules["amounts.$topicId.$round"] = ['required', 'regex:/^\d{1,5}([.,]\d{1,2})?$/'];
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = ['paymentInterval' => trans('Payment interval')];
        foreach ($this->topics as $topicId => $topic) {
            foreach ($topic['rounds'] as $round) {
                $attributes["amounts.$topicId.$round"] = trans(
                    'Offer round :number for :topic',
                    ['number' => $round, 'topic' => $topic['name']]
                );
            }
        }

        return $attributes;
    }

    /**
     * Formats without thousand separators so the value stays valid as input.
     */
    private static function formatNumber(float $value): string
    {
        $formatted = number_format($value, 2, ',', '');

        return str_ends_with($formatted, ',00') ? substr($formatted, 0, -3) : $formatted;
    }
}
