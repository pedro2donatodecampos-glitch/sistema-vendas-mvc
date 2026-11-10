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
use App\Controllers\CartController;
use App\Controllers\CustomerController;
use App\Controllers\ProductController;
use App\Controllers\ReportController;

// Front Controller: toda requisição passa por aqui antes de chegar a um Controller.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$router = new Router();
$router->get('/', CartController::class, 'index');

// 1 - Cadastro de clientes (CRUD)
$router->get('/customers', CustomerController::class, 'index');
$router->get('/customers/create', CustomerController::class, 'create');
$router->post('/customers', CustomerController::class, 'store');
$router->get('/customers/{id}/edit', CustomerController::class, 'edit');
$router->post('/customers/{id}/update', CustomerController::class, 'update');
$router->post('/customers/{id}/delete', CustomerController::class, 'delete');

// Cadastro de produtos (formulário do PDV da aula)
$router->get('/products', ProductController::class, 'index');
$router->get('/products/create', ProductController::class, 'create');
$router->post('/products', ProductController::class, 'store');
$router->get('/products/{id}/edit', ProductController::class, 'edit');
$router->post('/products/{id}/update', ProductController::class, 'update');
$router->post('/products/{id}/delete', ProductController::class, 'delete');

// 2 - Carrinho de vendas (CRUD de itens + finalização)
$router->get('/sales/new', CartController::class, 'index');
$router->post('/cart/items', CartController::class, 'addItem');
$router->post('/cart/items/{id}', CartController::class, 'updateItem');
$router->post('/cart/items/{id}/remove', CartController::class, 'removeItem');
$router->post('/cart/clear', CartController::class, 'clear');
$router->post('/sales/checkout', CartController::class, 'checkout');

// 3 - Relatórios de vendas (consultar, cancelar, excluir, exportar)
$router->get('/reports', ReportController::class, 'index');
$router->get('/reports/export', ReportController::class, 'export');
$router->get('/sales/{id}', ReportController::class, 'show');
$router->post('/sales/{id}/cancel', ReportController::class, 'cancel');
$router->post('/sales/{id}/delete', ReportController::class, 'delete');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
