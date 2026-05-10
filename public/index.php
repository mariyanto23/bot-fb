<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    require dirname(__DIR__) . '/app/helpers/functions.php';
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

use App\Core\App;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

App::bootstrap();
Session::start();

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
$router->dispatch(new Request());
