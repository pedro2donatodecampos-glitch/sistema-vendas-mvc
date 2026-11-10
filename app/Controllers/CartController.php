<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Repositories\CustomerRepository;
use App\Repositories\ProductRepository;
use App\Services\PaymentMethodFactory;
use App\Services\SaleService;

/**
 * Controller da tela "Carrinho de vendas":
 * Create = adicionar item, Read = ver carrinho, Update = alterar quantidade,
 * Delete = remover item / esvaziar. Finalizar transforma o carrinho em venda.
 */
final class CartController
{
    private ProductRepository $products;
    private CustomerRepository $customers;
    private SaleService $saleService;
    private Cart $cart;

    public function __construct()
    {
        $db = Database::connection();
        $this->products = new ProductRepository($db);
        $this->customers = new CustomerRepository($db);
        $this->saleService = new SaleService($db);
        $this->cart = new Cart();
    }

    public function index(): void
    {
        $items = [];
        $total = 0.0;
        foreach ($this->cart->items() as $productId => $quantity) {
            $product = $this->products->find((int) $productId);
            if (!$product) {
                $this->cart->remove((int) $productId);
                continue;
            }
            $product['cart_quantity'] = (int) $quantity;
            $product['line_total'] = (float) $product['unit_price'] * (int) $quantity;
            $total += $product['line_total'];
            $items[] = $product;
        }

        View::render('sales/cart', [
            'title' => 'Carrinho de vendas',
            'products' => $this->products->available(),
            'customers' => $this->customers->all(),
            'paymentMethods' => PaymentMethodFactory::all(),
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function addItem(): void
    {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $product = $productId ? $this->products->find($productId) : null;
        if (!$product || !$quantity || $quantity < 1) {
            $this->back('error', 'Selecione um produto disponível e uma quantidade válida.');
        }
        if (Product::isExpired($product['expires_at'])) {
            $this->back('error', 'Este produto está vencido e não pode ser vendido.');
        }
        if ($this->cart->quantityOf($productId) + $quantity > (int) $product['stock']) {
            $this->back('error', 'A quantidade solicitada excede o estoque disponível.');
        }

        $this->cart->add($productId, $quantity);
        $this->back('success', 'Produto adicionado ao carrinho.');
    }

    public function updateItem(string $id): void
    {
        $product = $this->products->find((int) $id);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if (!$product || !$this->cart->has((int) $id) || !$quantity || $quantity < 1 || $quantity > (int) $product['stock']) {
            $this->back('error', 'Informe uma quantidade válida dentro do estoque disponível.');
        }

        $this->cart->set((int) $id, $quantity);
        $this->back('success', 'Quantidade atualizada.');
    }

    public function removeItem(string $id): void
    {
        $this->cart->remove((int) $id);
        $this->back('success', 'Produto removido do carrinho.');
    }

    public function clear(): void
    {
        $this->cart->clear();
        $this->back('success', 'Carrinho esvaziado.');
    }

    public function checkout(): void
    {
        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT) ?: null;
        try {
            $saleId = $this->saleService->checkout(
                $this->cart->items(),
                $customerId,
                (string) ($_POST['payment_method'] ?? '')
            );
        } catch (\DomainException $exception) {
            $this->back('error', $exception->getMessage());
        }

        $this->cart->clear();
        flash('success', 'Venda #' . $saleId . ' finalizada com sucesso.');
        redirect('/sales/' . $saleId);
    }

    private function back(string $type, string $message): never
    {
        flash($type, $message);
        redirect('/sales/new');
    }
}
