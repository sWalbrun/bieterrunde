<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Exceptions\OverlappingBidderRoundException;
use App\Filament\Resources\BidderRoundResource;
use App\Filament\Resources\TopicResource;
use App\Models\BidderRound;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

use function trans;

/**
 * Guided step by step creation of a bidder round — same result as creating
 * the round and its topics through the single resources by hand.
 */
class CreateBidderRound extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = BidderRoundResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    /**
     * @return Step[]
     */
    protected function getSteps(): array
    {
        return [
            $this->periodStep(),
            $this->topicsStep(),
            $this->participantsStep(),
            $this->reviewStep(),
        ];
    }

    private function periodStep(): Step
    {
        return Step::make(trans('Submission period'))
            ->description(trans('Within this period the members can place and change their offers.'))
            ->icon('heroicon-o-calendar')
            ->schema([
                DatePicker::make(BidderRound::COL_START_OF_SUBMISSION)
                    ->label(trans('Start of submission'))
                    ->required(),
                DatePicker::make(BidderRound::COL_END_OF_SUBMISSION)
                    ->label(trans('End of submission'))
                    ->required()
                    ->afterOrEqual(BidderRound::COL_START_OF_SUBMISSION)
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                            $start = $get(BidderRound::COL_START_OF_SUBMISSION);
                            if (blank($start) || blank($value)) {
                                return;
                            }
                            $isOverlapping = BidderRound::query()
                                ->where(BidderRound::COL_END_OF_SUBMISSION, '>=', $start)
                                ->where(BidderRound::COL_START_OF_SUBMISSION, '<=', $value)
                                ->exists();
                            if ($isOverlapping) {
                                $fail(trans('This bidder round overlaps with an existing one.'));
                            }
                        },
                    ]),
                Textarea::make(BidderRound::COL_NOTE)
                    ->translateLabel()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    private function topicsStep(): Step
    {
        return Step::make(trans('Products'))
            ->description(trans('Every product gets its own bidding with a target amount. This step can be left empty — products can be added later as well.'))
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                Repeater::make('topics')
                    ->label(trans('Products'))
                    ->schema(TopicResource::createSchema())
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state[Topic::COL_NAME] ?? null)
                    ->addActionLabel(trans('Add product')),
            ]);
    }

    private function participantsStep(): Step
    {
        return Step::make(trans('Participants'))
            ->description(trans('Applies to all products of this round. Individual products can be adjusted afterwards on the product page.'))
            ->icon('heroicon-o-users')
            ->visible(fn (Get $get) => filled($get('topics')))
            ->schema([
                CheckboxList::make('participants')
                    ->label(trans('Participating members'))
                    ->options(
                        fn () => User::currentlyActive()
                            ->orderBy(User::COL_NAME)
                            ->get()
                            ->mapWithKeys(fn (User $user) => [$user->id => "$user->name ($user->email)"])
                    )
                    ->default(fn () => User::currentlyActive()->pluck(User::COL_ID)->all())
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(2),
            ]);
    }

    private function reviewStep(): Step
    {
        return Step::make(trans('Review & create'))
            ->icon('heroicon-o-check-circle')
            ->schema([
                Placeholder::make('reviewPeriod')
                    ->label(trans('Submission period'))
                    ->content(function (Get $get) {
                        $start = $get(BidderRound::COL_START_OF_SUBMISSION);
                        $end = $get(BidderRound::COL_END_OF_SUBMISSION);
                        if (blank($start) || blank($end)) {
                            return '–';
                        }

                        return Carbon::parse($start)->format('d.m.Y').' – '.Carbon::parse($end)->format('d.m.Y');
                    }),
                Placeholder::make('reviewTopics')
                    ->label(trans('Products'))
                    ->content(function (Get $get) {
                        $topics = collect($get('topics') ?? [])->filter(fn (array $topic) => filled($topic[Topic::COL_ROUNDS] ?? null));
                        if ($topics->isEmpty()) {
                            return trans('No products yet — all active members get linked automatically once a product is added.');
                        }

                        return new HtmlString(
                            $topics->map(fn (array $topic) => e(sprintf(
                                '%s — %s %s, %s €',
                                $topic[Topic::COL_NAME] ?: trans('Product'),
                                $topic[Topic::COL_ROUNDS],
                                trans('Rounds'),
                                $topic[Topic::COL_TARGET_AMOUNT] ?? '–',
                            )))->implode('<br>')
                        );
                    }),
                Placeholder::make('reviewParticipants')
                    ->label(trans('Participants'))
                    ->visible(fn (Get $get) => filled($get('topics')))
                    ->content(fn (Get $get) => trans(':selected of :total active members participate', [
                        'selected' => count($get('participants') ?? []),
                        'total' => User::currentlyActive()->count(),
                    ])),
                Placeholder::make('reviewHint')
                    ->hiddenLabel()
                    ->content(trans('Creating the round does not send any mails. Use "Announce start" on the rounds list once the bidding should begin.')),
            ]);
    }

    /**
     * @throws ValidationException
     */
    protected function handleRecordCreation(array $data): BidderRound
    {
        try {
            return DB::transaction(function () use ($data) {
                /** @var BidderRound $bidderRound */
                $bidderRound = static::getModel()::create(Arr::only($data, [
                    BidderRound::COL_START_OF_SUBMISSION,
                    BidderRound::COL_END_OF_SUBMISSION,
                    BidderRound::COL_NOTE,
                ]));

                foreach ($data['topics'] ?? [] as $topicData) {
                    /** @var Topic $topic */
                    // Creating the topic auto-links all currently active members …
                    $topic = $bidderRound->topics()->create($topicData);
                    // … which get pruned down to the wizard's selection
                    if (isset($data['participants'])) {
                        $topic->shares()->whereNotIn(Share::COL_FK_USER, $data['participants'])->delete();
                    }
                }

                return $bidderRound;
            });
        } catch (OverlappingBidderRoundException) {
            Notification::make()
                ->title(trans('Overlapping Bidder Round'))
                ->body(trans('This bidder round overlaps with an existing one.'))
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
            ]);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(trans('Bidder round created'))
            ->body(trans('No mails have been sent yet — use "Announce start" on the rounds list once the bidding should begin.'));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
