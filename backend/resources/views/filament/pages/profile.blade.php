<x-filament::page>
    <div class="max-w-xl space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="admin-locale">
                {{ __('admin.profile.language') }}
            </label>
            <select
                id="admin-locale"
                wire:model="selectedLocale"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
                <option value="en">{{ __('admin.languages.en') }}</option>
                <option value="cs">{{ __('admin.languages.cs') }}</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('admin.profile.language_help') }}
            </p>
            <div class="mt-4">
                <button
                    type="button"
                    wire:click="saveLocale"
                    class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    {{ __('admin.profile.save_language') }}
                </button>
            </div>
        </div>
    </div>
</x-filament::page>
