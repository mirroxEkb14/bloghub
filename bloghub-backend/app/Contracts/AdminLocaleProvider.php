<?php

namespace App\Contracts;

interface AdminLocaleProvider
{
    public function get(): string;
}
