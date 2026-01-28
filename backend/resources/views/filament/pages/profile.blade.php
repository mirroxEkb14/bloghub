<x-filament::page>
    <div class="max-w-2xl">
        <div class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/60">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('admin.profile.language') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('admin.profile.language_help') }}
                </p>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition hover:border-primary-400 hover:shadow-md dark:border-gray-800 dark:bg-gray-950">
                    <input
                        type="radio"
                        name="admin-locale"
                        value="en"
                        wire:model="selectedLocale"
                        class="mt-1 h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900"
                    />
                    <span class="flex flex-col gap-1">
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            {{ __('admin.languages.en') }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.profile.language_option_en_help') }}
                        </span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition hover:border-primary-400 hover:shadow-md dark:border-gray-800 dark:bg-gray-950">
                    <input
                        type="radio"
                        name="admin-locale"
                        value="cs"
                        wire:model="selectedLocale"
                        class="mt-1 h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900"
                    />
                    <span class="flex flex-col gap-1">
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            {{ __('admin.languages.cs') }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('admin.profile.language_option_cs_help') }}
                        </span>
                    </span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('admin.profile.selected_language') }}
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $selectedLocale === 'cs' ? __('admin.languages.cs') : __('admin.languages.en') }}</span>
                </span>
                <button
                    type="button"
                    wire:click="saveLocale"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    {{ __('admin.profile.save_language') }}
                </button>
            </div>
        </div>
    </div>
</x-filament::page>
