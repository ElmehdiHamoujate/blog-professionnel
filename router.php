<?php
// Built-in PHP server router — handles URL rewriting like .htaccess
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Block access to sensitive files
$blocked = ['.env', '.sqlite', '.sqlite-wal', '.sqlite-shm', '.log'];
foreach ($blocked as $ext) {
    if (str_ends_with($uri, $ext)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

// Serve real static files directly (css, js, images, etc.)
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Everything else goes to index.php
require_once __DIR__ . '/index.php';
