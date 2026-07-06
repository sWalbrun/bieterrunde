<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The landing page of the user area.
 */
class Dashboard extends Component
{
    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('livewire.dashboard', [
            'userName' => $user->name,
        ]);
    }
}
