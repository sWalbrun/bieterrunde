<?php

namespace App\Livewire;

use App\Enums\EnumRole;
use App\Enums\EnumTenantRequestStatus;
use App\Models\TenantRequest;
use App\Models\User;
use App\Notifications\TenantRequestReceived;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Public form (linked from the login page) for interested Solawis to
 * request a test account. Requests are reviewed by the super admins.
 */
#[Layout('components.layouts.guest')]
class RequestTestAccount extends Component
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 3600;

    public string $name = '';

    public string $email = '';

    public string $solawiName = '';

    public ?string $websiteUrl = null;

    /** Honeypot — invisible to humans, bots fill it in */
    public string $website = '';

    public bool $submitted = false;

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:125'],
            'email' => ['required', 'email', 'max:125'],
            'solawiName' => ['required', 'string', 'max:125'],
            'websiteUrl' => ['nullable', 'url', 'max:255'],
        ]);

        if (filled($this->website)) {
            // Bots get a silent fake success
            $this->submitted = true;

            return;
        }

        $executed = RateLimiter::attempt(
            'tenant-request:'.request()->ip(),
            self::MAX_ATTEMPTS,
            fn () => $this->storeRequest(),
            self::DECAY_SECONDS
        );

        if (! $executed) {
            $this->addError('email', trans('Too many attempts. Please wait a moment.'));

            return;
        }

        $this->submitted = true;
    }

    private function storeRequest(): void
    {
        $emailAlreadyKnown = User::query()
            ->withoutGlobalScopes()
            ->where(User::COL_EMAIL, '=', $this->email)
            ->exists()
            || TenantRequest::query()
                ->where(TenantRequest::COL_EMAIL, '=', $this->email)
                ->where(TenantRequest::COL_STATUS, '=', EnumTenantRequestStatus::PENDING)
                ->exists();
        if ($emailAlreadyKnown) {
            // Show the same success message — no information leak about existing accounts
            return;
        }

        $request = TenantRequest::query()->create([
            TenantRequest::COL_NAME => $this->name,
            TenantRequest::COL_EMAIL => $this->email,
            TenantRequest::COL_SOLAWI_NAME => $this->solawiName,
            TenantRequest::COL_WEBSITE_URL => $this->websiteUrl ?: null,
        ]);

        Notification::send(
            User::query()->withoutGlobalScopes()->where(User::COL_ROLE, '=', EnumRole::SUPER_ADMIN)->get(),
            new TenantRequestReceived($request)
        );
    }

    public function render(): View
    {
        return view('livewire.request-test-account');
    }
}
