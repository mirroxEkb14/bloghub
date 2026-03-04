@php
    use Illuminate\Support\Str;$profile = $record->creatorProfile;
    $rawName = $profile?->display_name ?? '';
    $displayName = Str::limit($rawName, 30);
    $initial = $rawName !== '' ? mb_strtoupper(mb_substr($rawName, 0, 1)) : '?';
    $avatarUrl = $profile?->profile_avatar_url ?? null;
@endphp

<div style="display: flex; align-items: center; gap: 0.75rem;">
    <div
        style="display: flex; height: 36px; width: 36px; align-items: center; justify-content: center; border-radius: 10px; background-color: #1f2937; font-size: 0.875rem; font-weight: 600; color: #f59e0b; overflow: hidden; flex-shrink: 0;">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="" style="width: 100%; height: 100%; object-fit: cover;"/>
        @else
            {{ $initial }}
        @endif
    </div>
    <div style="min-width: 0;">
        <div
            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.875rem; font-weight: 600; color: #f9fafb;">
            {{ $displayName }}
        </div>
    </div>
</div>
