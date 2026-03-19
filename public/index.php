<?php

declare(strict_types=1);

use App\Core\Router;

define('BASE_PATH', dirname(__DIR__));

session_start();

require_once BASE_PATH . '/app/helpers/i18n.php';

set_language_from_request();

spl_autoload_register(static function (string $className): void {
    $prefixes = [
        'App\\' => BASE_PATH . '/app/',
        'Config\\' => BASE_PATH . '/config/',
    ];

    foreach ($prefixes as $prefix => $baseDirectory) {
        if (strpos($className, $prefix) !== 0) {
            continue;
        }

        $relativeClass = substr($className, strlen($prefix));
        $filePath = $baseDirectory . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
});

$router = new Router();

$routes = require BASE_PATH . '/routes/web.php';
$routes($router);

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
