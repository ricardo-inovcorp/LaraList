<?php

// Roteador para o servidor PHP embutido

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Serve o index.php para requisições de front-controller
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Inclui o front-controller do Laravel
require_once __DIR__ . '/public/index.php'; 