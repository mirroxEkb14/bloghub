<x-filament::page>
    <div class="max-w-xl space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="admin-locale">
                {{ __('admin.profile.language') }}
            </label>
            <select
                id="admin-locale"
                wire:model="locale"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
                <option value="en">{{ __('admin.languages.en') }}</option>
                <option value="cs">{{ __('admin.languages.cs') }}</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('admin.profile.language_help') }}
            </p>
        </div>
    </div>
</x-filament::page>
