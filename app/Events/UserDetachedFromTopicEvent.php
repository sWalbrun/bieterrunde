<?php

namespace App\Events;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UserDetachedFromTopicEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Collection<User>
     */
    public readonly Collection $users;

    /**
     * @param  User|Collection<User>  $users
     */
    public function __construct(
        User|Collection $users,
        public readonly Topic $topic
    ) {
        if ($users instanceof Collection) {
            $this->users = $users;
        } else {
            $this->users = collect([$users]);
        }
    }
}
