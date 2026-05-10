<?php

namespace App\helpers;

use App\Core\Session;

final class AuthHelper
{
    public static function user(): ?array
    {
        return Session::user();
    }

    public static function check(): bool
    {
        return Session::isAuthenticated();
    }
}
