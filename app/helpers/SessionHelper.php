<?php

namespace App\helpers;

use App\Core\Session;

final class SessionHelper
{
    public static function flash(string $key, mixed $value = null): mixed
    {
        return func_num_args() === 2 ? Session::flash($key, $value) : Session::flash($key);
    }
}
