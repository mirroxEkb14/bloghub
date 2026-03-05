@php
    use Illuminate\Support\Str;$name = $getState() ?? '';
    $initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
    $username = $record->user?->username ?? '';
    $nameLimit = 25;
    $usernameLimit = 20;
    $nameDisplay = Str::limit($name, $nameLimit);
    $usernameDisplay = Str::limit($username, $usernameLimit);
@endphp

<div style="display: flex; align-items: center; gap: 0.75rem; max-width: 180px;">
    <div
        style="display: flex; height: 36px; width: 36px; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 10px; background-color: #1f2937; font-size: 0.875rem; font-weight: 600; color: #f59e0b;">
        {{ $initial }}
    </div>
    <div style="min-width: 0; overflow: hidden;">
        <div
            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.875rem; font-weight: 600; color: #f9fafb;"
            @if(mb_strlen($name) > $nameLimit) title="{{ $name }}" @endif>
            {{ $nameDisplay }}
        </div>
        @if($username !== '')
            <div
                style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.75rem; color: #9ca3af;"
                @if(mb_strlen($username) > $usernameLimit) title="{{ $username }}" @endif>
                {{ $usernameDisplay }}
            </div>
        @endif
    </div>
</div>
