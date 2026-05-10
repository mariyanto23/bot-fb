<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $viewFile = app_path('views/' . $view . '.php');
        if (!is_file($viewFile)) {
            Response::abort(500, 'View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = app_path('views/' . $layout . '.php');
        if (!is_file($layoutFile)) {
            Response::abort(500, 'Layout not found: ' . $layout);
        }

        require $layoutFile;
    }
}
