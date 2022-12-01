<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BidderRound;
use Illuminate\Auth\Access\HandlesAuthorization;

class BidderRoundPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_bidder::round');
    }

    public function view(User $user, BidderRound $bidderRound)
    {
        return $user->can('view_bidder::round');
    }

    public function create(User $user)
    {
        return $user->can('create_bidder::round');
    }

    public function update(User $user, BidderRound $bidderRound)
    {
        return $user->can('update_bidder::round');
    }

    public function delete(User $user, BidderRound $bidderRound)
    {
        return $user->can('delete_bidder::round');
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_bidder::round');
    }

    public function forceDelete(User $user, BidderRound $bidderRound)
    {
        return $user->can('force_delete_bidder::round');
    }

    public function forceDeleteAny(User $user)
    {
        return $user->can('force_delete_any_bidder::round');
    }

    public function restore(User $user, BidderRound $bidderRound)
    {
        return $user->can('restore_bidder::round');
    }

    public function restoreAny(User $user)
    {
        return $user->can('restore_any_bidder::round');
    }

    public function replicate(User $user, BidderRound $bidderRound)
    {
        return $user->can('replicate_bidder::round');
    }

    public function reorder(User $user)
    {
        return $user->can('reorder_bidder::round');
    }
}
