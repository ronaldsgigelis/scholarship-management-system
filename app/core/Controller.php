<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = BASE_PATH . '/app/views/' . $view . '.php';

        if (! file_exists($viewPath)) {
            http_response_code(404);
            echo t('messages.view_not_found');
            return;
        }

        ob_start();
        require $viewPath;
        echo translate_output((string) ob_get_clean());
    }
}
