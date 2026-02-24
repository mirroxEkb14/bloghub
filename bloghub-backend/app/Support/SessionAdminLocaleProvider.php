<?php

namespace App\Support;

use App\Contracts\AdminLocaleProvider;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session;

final class SessionAdminLocaleProvider implements AdminLocaleProvider
{
    public function __construct(
        private Session $session,
        private AuthFactory $auth,
    ) {}

    public function get(): string
    {
        $sessionLocale = $this->session->get(AdminLocale::ADMIN_LOCALE_SESSION_KEY);

        if ($sessionLocale !== null && AdminLocale::isValid($sessionLocale)) {
            return $sessionLocale;
        }

        $userLocale = $this->auth->guard()->user()?->locale;

        if ($userLocale !== null && AdminLocale::isValid($userLocale)) {
            return $userLocale;
        }

        return config('app.locale');
    }
}
