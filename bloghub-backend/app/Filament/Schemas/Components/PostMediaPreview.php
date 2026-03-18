<?php

namespace App\Filament\Schemas\Components;

use Filament\Schemas\Components\Component;

class PostMediaPreview extends Component
{
    protected string $view = 'filament.schemas.components.post-media-preview';

    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }
}
