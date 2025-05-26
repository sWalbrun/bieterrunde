<?php

namespace App\Listeners;

use App\Events\UserDetachedFromTopicEvent;
use App\Models\User;

class DeleteAbandonedSharesAndOffers
{
    public function handle(UserDetachedFromTopicEvent $event): void
    {
        $users = $event->users;
        $topic = $event->topic;

        $users->each(function (User $user) use ($topic) {
            $user->offersForTopic($topic)->delete();
            $topic->sharesForUser($user)->delete();
        });
    }
}
