<?php

namespace App\Support;

final class AdminTimezone
{
    public const ADMIN_TZ_SESSION_KEY = 'admin_timezone';

    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}
