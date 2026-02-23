<?php

namespace App\Support;

use App\Enums\SubStatus;
use App\Filament\Resources\SubscriptionResource\SubscriptionResource;
use App\Models\Subscription;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResourceSupport
{
    /** SP-19: sub_status max length */
    public const SUB_STATUS_MAX_LENGTH = 20;

    private function __construct()
    {
    }

    public static function subStatusOptions(): array
    {
        $options = [];
        foreach (SubStatus::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }

    public static function recordViewUrl(Subscription $record): string
    {
        return SubscriptionResource::getUrl('view', ['record' => $record]);
    }

    public static function tableModifyQueryUsing(): Closure
    {
        return static function (Builder $query): Builder {
            return $query->with(['user', 'tier']);
        };
    }
}
