<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $publicRoot = realpath(__DIR__);
    $requestedFile = realpath(__DIR__ . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/'));
    if ($publicRoot !== false && $requestedFile !== false
        && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
        && is_file($requestedFile)
    ) {
        return false;
    }
}

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;
use App\Controllers\CustomerController;
use App\Controllers\SalesController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$router = new Router();
$router->get('/', SalesController::class, 'cart');
$router->get('/customers', CustomerController::class, 'index');
$router->get('/customers/create', CustomerController::class, 'create');
$router->post('/customers', CustomerController::class, 'store');
$router->get('/customers/{id}/edit', CustomerController::class, 'edit');
$router->post('/customers/{id}/update', CustomerController::class, 'update');
$router->post('/customers/{id}/delete', CustomerController::class, 'delete');
$router->get('/sales/new', SalesController::class, 'cart');
$router->post('/cart/items', SalesController::class, 'addItem');
$router->post('/cart/items/{id}', SalesController::class, 'updateItem');
$router->post('/cart/items/{id}/remove', SalesController::class, 'removeItem');
$router->post('/cart/clear', SalesController::class, 'clearCart');
$router->post('/sales/checkout', SalesController::class, 'checkout');
$router->get('/sales/{id}', SalesController::class, 'detail');
$router->get('/reports', SalesController::class, 'reports');
$router->get('/reports/export', SalesController::class, 'export');
$router->post('/sales/{id}/cancel', SalesController::class, 'cancel');
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
