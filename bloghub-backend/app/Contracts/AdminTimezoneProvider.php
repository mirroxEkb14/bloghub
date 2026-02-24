<?php

namespace App\Contracts;

interface AdminTimezoneProvider
{
    public function get(): string;
}
