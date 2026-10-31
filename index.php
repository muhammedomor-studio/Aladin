<?php
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

use App\Router;

$router = new Router();
$router->get('/', fn() => response_json([
    'ok' => true,
    'name' => env('SHOP_NAME', 'Blogger DropBazar Backend'),
    'version' => '1.0.0',
    'message' => 'Backend is running.'
]));
$router->get('/health', fn() => response_json(['ok' => true, 'status' => 'healthy']));
$router->get('/api/products', fn() => App\Controllers\CatalogController::products());
$router->get('/api/products/{id}', fn($id) => App\Controllers\CatalogController::product($id));
$router->get('/api/categories', fn() => App\Controllers\CatalogController::categories());
$router->get('/api/districts', fn() => App\Controllers\CatalogController::districts());
$router->get('/api/districts/{id}/thanas', fn($id) => App\Controllers\CatalogController::thanas($id));
$router->post('/api/order-preview', fn() => App\Controllers\OrderController::preview());
$router->post('/api/orders', fn() => App\Controllers\OrderController::place());
$router->get('/api/orders/{id}', fn($id) => App\Controllers\OrderController::status($id));
$router->dispatch();
