<?php

namespace App\Models;

use App\BidderRound\Participant;
use App\Enums\EnumContributionGroup;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property float targetAmount
 * @property Carbon startOfSubmission
 * @property Carbon endOfSubmission
 * @property Carbon validFrom
 * @property Carbon validTo
 * @property int countOffers
 * @property string note
 * @property Collection<Offer> offers
 * @property BidderRoundReport $bidderRoundReport
 */
class BidderRound extends BaseModel
{
    use HasFactory;
    use BidderRoundRelations;

    public const TABLE = 'bidderRound';
    public const AVERAGE_NEW_MEMBER_INCREASE_RATE = 12.5;

    protected $table = self::TABLE;

    public const COL_TARGET_AMOUNT = 'targetAmount';
    public const COL_START_OF_SUBMISSION = 'startOfSubmission';
    public const COL_END_OF_SUBMISSION = 'endOfSubmission';
    public const COL_VALID_FROM = 'validFrom';
    public const COL_VALID_TO = 'validTo';
    public const COL_COUNT_OFFERS = 'countOffers';
    public const COL_NOTE = 'note';

    protected $casts = [
        self::COL_START_OF_SUBMISSION => 'date',
        self::COL_END_OF_SUBMISSION => 'date',
        self::COL_VALID_FROM => 'date',
        self::COL_VALID_TO => 'date',
    ];

    protected $fillable = [
        self::COL_TARGET_AMOUNT,
        self::COL_START_OF_SUBMISSION,
        self::COL_END_OF_SUBMISSION,
        self::COL_VALID_FROM,
        self::COL_VALID_TO,
        self::COL_COUNT_OFFERS,
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, Offer::COL_FK_BIDDER_ROUND);
    }

    /**
     * Returns the builder for all offers which has a given user made for this round.
     *
     * @param User $user
     *
     * @return HasMany
     */
    public function offerFor(User $user): HasMany
    {
        return $this->offers()->whereBelongsTo($user, $user->identifier());
    }

    /**
     * @return Collection<Participant>
     */
    public function participants(): Collection
    {
        return User::bidderRoundParticipants()->get();
    }

    /**
     * Returns true in case the user still has the possibility to change/create her/his offer.
     *
     * @return bool
     */
    public function isOfferStillPossible(): bool
    {
        return !$this->bidderRoundReport()->exists()
            && $this->bidderRoundBetweenNow();
    }

    /**
     * Returns true in case a given user has made all offers needed for this round.
     *
     * @param User $user
     *
     * @return bool
     */
    public function allOffersGivenFor(User $user): bool
    {
        return $this
            ->offerFor($user)
            ->whereNotNull(Offer::COL_AMOUNT)
            ->count() === $this->countOffers;
    }

    /**
     * @return Collection<self>
     */
    public static function orderedRounds(): Collection
    {
        return self::query()
            ->orderBy(self::COL_VALID_FROM, 'DESC')
            ->orderBy(self::COL_VALID_TO, 'DESC')
            ->get();
    }

    public function bidderRoundReport(): HasOne
    {
        return $this->hasOne(BidderRoundReport::class, BidderRoundReport::COL_FK_BIDDER_ROUND);
    }

    public function bidderRoundBetweenNow(): bool
    {
        return Carbon::now()->isBetween($this->startOfSubmission->startOfDay(), $this->endOfSubmission->endOfDay());
    }

    public function getReferenceAmountFor(User $user, int $roundIndex): ?string
    {
        if ($user->contributionGroup === EnumContributionGroup::SUSTAINING_MEMBER) {
            return '>= ' . $this->formatAmount(1 + ($roundIndex * 2));
        }

        $targetAmountPerMonth = $this->targetAmount / 12;

        $participants = User::bidderRoundParticipants()->get();

        if ($participants->isEmpty()) {
            return trans('Betrag');
        }

        $countNew = $participants
            ->filter(fn (User $user) => $user->isNewMember && $user->contributionGroup === EnumContributionGroup::FULL_MEMBER)
            ->map(fn (User $user) => $user->countShares)
            ->sum();
        $countOld = $participants
            ->filter(fn (User $user) => !$user->isNewMember && $user->contributionGroup === EnumContributionGroup::FULL_MEMBER)
            ->map(fn (User $user) => $user->countShares)
            ->sum();

        $countSustainingMember = $participants
            ->filter(fn (User $user) => $user->contributionGroup === EnumContributionGroup::SUSTAINING_MEMBER)
            ->count();

        if (($countNew + $countOld) === 0) {
            $referenceAmountForFullMember = 0;
        }

        $referenceAmountForFullMember ??= (-self::AVERAGE_NEW_MEMBER_INCREASE_RATE * $countNew + $targetAmountPerMonth - $countSustainingMember) / ($countNew + $countOld);
        if ($user->isNewMember) {
            return trans('z. B. ')
                . $this->formatAmount($referenceAmountForFullMember + self::AVERAGE_NEW_MEMBER_INCREASE_RATE + $roundIndex * 3)
                . ' ('
                . $this->formatAmount($referenceAmountForFullMember + $roundIndex * 3)
                . ' + '
                . $this->formatAmount(self::AVERAGE_NEW_MEMBER_INCREASE_RATE)
                . ')';
        }

        return trans('z. B. ') . $this->formatAmount($referenceAmountForFullMember + $roundIndex * 2);
    }

    public function __toString()
    {
        return trans('Bieterrunde ') . ($this->validFrom ? $this->validFrom->format('Y') : '');
    }

    private function formatAmount(string $referenceAmountForFullMember): string
    {
        return number_format(ceil($referenceAmountForFullMember), 2, ',', '.');
    }
}
