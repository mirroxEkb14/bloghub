<x-filament-panels::page>
    <form wire:submit="save">
        <div class="mb-6">
            {{ $this->form }}
        </div>

        <div style="margin-top: 1rem;">
            <x-filament::button type="submit">
                {{ __('filament.profile.save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
