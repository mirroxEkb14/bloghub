<x-filament::page>
    <x-filament::form wire:submit="saveLocale">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            {{ __('admin.profile.save_language') }}
        </x-filament::button>
    </x-filament::form>
</x-filament::page>
