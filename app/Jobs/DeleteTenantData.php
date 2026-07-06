<?php

namespace App\Jobs;

use App\Models\BidderRound;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

/**
 * All tenants share one database, so deleting a tenant means deleting its
 * rows (not a database — the former DeleteDatabase job tried to drop one
 * that never existed). Models are deleted one by one so the observers
 * cascade shares, offers and topics.
 */
class DeleteTenantData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Tenant $tenant) {}

    public function handle(): void
    {
        User::query()
            ->withoutGlobalScopes()
            ->where(User::COL_FK_TENANT, '=', $this->tenant->id)
            ->get()
            ->each(fn (User $user) => $user->delete());

        BidderRound::query()
            ->withoutGlobalScopes()
            ->where(User::COL_FK_TENANT, '=', $this->tenant->id)
            ->get()
            ->each(fn (BidderRound $bidderRound) => $bidderRound->delete());

        File::deleteDirectory(base_path('storage/tenant'.$this->tenant->id));
    }
}
