<?php
// PHP built-in server router — handles root redirect and static files
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Redirect root to home.php
if ($uri === '/') {
    header('Location: /home.php');
    exit();
}

// Serve static files (css, js, images) directly
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$/', $uri)) {
    return false; // serve the file as-is
}

// Let PHP handle .php files
return false;
