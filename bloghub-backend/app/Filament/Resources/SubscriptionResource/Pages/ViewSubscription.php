<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource\SubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['tier_level'] = $this->getRecord()->tier?->level;

        return $data;
    }
}
