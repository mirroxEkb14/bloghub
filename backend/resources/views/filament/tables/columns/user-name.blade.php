@php
    $name = $getState() ?? '';
    $initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
@endphp

<div class="flex items-center gap-3">
    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-sm font-semibold text-amber-400">
        {{ $initial }}
    </div>
    <div class="min-w-0">
        <div class="truncate text-sm font-medium text-gray-100">
            {{ $name }}
        </div>
        <div class="truncate text-xs text-gray-500">
            {{ $record->username }}
        </div>
    </div>
</div>
