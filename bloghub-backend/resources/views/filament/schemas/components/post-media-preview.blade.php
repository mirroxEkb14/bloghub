@php
use App\Enums\MediaType;
use App\Support\StorageUrlSupport;

$url = $record?->media_url;
if ($url !== null && $url !== '' && ! str_starts_with($url, 'http')) {
    $url = StorageUrlSupport::publicUrl($url);
}
$type = $record?->media_type;
@endphp
<div class="grid gap-y-1.5">
    <label class="fi-fo-field-wrp-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
        {{ __('filament.posts.form.media_preview') }}
    </label>
    <x-filament::input.wrapper
        :disabled="true"
        :valid="true"
        class="fi-fo-text-input min-h-[2.25rem]"
    >
        <div class="px-3 py-2">
            @if ($url && $type)
                @if ($type === MediaType::Image || $type === MediaType::Gif)
                    <img src="{{ e($url) }}" alt="" class="max-h-80 w-full rounded object-contain object-left-top" loading="lazy" />
                @elseif ($type === MediaType::Video)
                    <video src="{{ e($url) }}" controls class="max-h-80 w-full rounded object-contain" preload="metadata"></video>
                @elseif ($type === MediaType::Audio)
                    <audio src="{{ e($url) }}" controls class="w-full" preload="metadata"></audio>
                @endif
            @else
                <span class="text-gray-500 dark:text-gray-400">{{ __('filament.posts.form.media_preview_empty') }}</span>
            @endif
        </div>
    </x-filament::input.wrapper>
</div>
