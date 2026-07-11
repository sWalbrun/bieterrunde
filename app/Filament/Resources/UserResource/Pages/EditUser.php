<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    /** @var User $user */
                    $user = $this->record;
                    $reason = UserResource::deletionBlockReason($user);
                    if ($reason !== null) {
                        Notification::make()->title($reason)->danger()->send();
                        $action->halt();
                    }
                }),
        ];
    }
}
