<?php

namespace App\helpers;

use App\Core\Response;

final class ResponseHelper
{
    public static function success(array $data = [], string $message = 'Success'): void
    {
        Response::json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    public static function error(string $message, int $status = 400, array $data = []): void
    {
        Response::json(['success' => false, 'message' => $message, 'data' => $data], $status);
    }
}
