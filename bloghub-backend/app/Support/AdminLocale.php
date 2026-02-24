<?php

namespace App\Support;

final class AdminLocale
{
    public const ADMIN_LOCALE_SESSION_KEY = 'admin_locale';

    public const SUPPORTED = ['en', 'cs'];

    public static function isValid(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }
}
