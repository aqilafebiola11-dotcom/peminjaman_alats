<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = Route::getRoutes();
echo "Routes containing 'dashboard':\n";
foreach($routes as $route) {
    if (strpos($route->uri(), 'dashboard') !== false) {
        echo "  {$route->methods()[0]}: {$route->uri()}\n";
    }
}

echo "\nRoutes for admin panel:\n";
foreach($routes as $route) {
    if (strpos($route->uri(), 'admin') !== false) {
        echo "  {$route->methods()[0]}: {$route->uri()}\n";
    }
}
