<?php

namespace App\Observers;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->password ??= Hash::make(Str::random(10));
    }

    public function deleting(User $user): void
    {
        $user->topics()->each(
            function (Topic $topic) use ($user) {
                $topic->sharesForUser($user)->delete();
                $user->offersForTopic($topic)->delete();
            }
        );
    }
}
