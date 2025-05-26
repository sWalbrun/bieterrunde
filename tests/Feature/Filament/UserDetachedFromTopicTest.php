<?php

use App\Enums\ShareValue;
use App\Filament\Resources\TopicResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Livewire\Livewire;

it("deletes the user's shares and offers when the user is detached from a topic", function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    $topic->users()->attach($user);

    Offer::factory()->for($topic)->for($user)->create();
    $topic->shares()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $topic,
    ])
        ->callTableAction('detach', $user);

    expect($topic->sharesForUser($user)->doesntExist())->toBeTrue()
        ->and($user->offersForTopic($topic)->doesntExist())->toBeTrue();
});

it('bulk deletes the users shares and offers when the user is detached from a topic', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    $topic->users()->attach($user);

    Offer::factory()->for($topic)->for($user)->create();
    $topic->shares()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $topic,
    ])
        ->callTableBulkAction('detach', [$user->id]);

    expect($topic->sharesForUser($user)->doesntExist())->toBeTrue()
        ->and($user->offersForTopic($topic)->doesntExist())->toBeTrue();
});
