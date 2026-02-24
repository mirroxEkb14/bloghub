<?php

namespace App\Support;

use App\Contracts\AdminTimezoneProvider;
use Illuminate\Contracts\Session\Session;

final class SessionAdminTimezoneProvider implements AdminTimezoneProvider
{
    public function __construct(
        private Session $session,
    ) {}

    public function get(): string
    {
        $sessionTimezone = $this->session->get(AdminTimezone::ADMIN_TZ_SESSION_KEY);

        if ($sessionTimezone !== null && AdminTimezone::isValid($sessionTimezone)) {
            return $sessionTimezone;
        }

        return config('app.timezone');
    }
}
