@php
    $name = $getState() ?? '';
    $initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
@endphp

<div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0; max-width: 100%;">
    <div style="display: flex; flex-shrink: 0; height: 44px; width: 44px; align-items: center; justify-content: center; border-radius: 10px; background-color: #1f2937; font-size: 1.125rem; font-weight: 600; color: #f59e0b;">
        {{ $initial }}
    </div>
    <div style="min-width: 0;">
        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.875rem; font-weight: 600; color: #f9fafb;">
            {{ $name }}
        </div>
        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.75rem; color: #9ca3af;">
            {{ $record->username }}
        </div>
    </div>
</div>
