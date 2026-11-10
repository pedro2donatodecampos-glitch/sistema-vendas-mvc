<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\CustomerRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\SaleService;

final class SalesController
{
    private \PDO $db;
    private ProductRepository $products;
    private CustomerRepository $customers;
    private SaleRepository $sales;
    private SaleService $saleService;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->products = new ProductRepository($this->db);
        $this->customers = new CustomerRepository($this->db);
        $this->sales = new SaleRepository($this->db);
        $this->saleService = new SaleService($this->db);
    }

    public function cart(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        $total = 0.0;
        foreach ($cart as $productId => $quantity) {
            $product = $this->products->find((int) $productId);
            if (!$product) {
                unset($_SESSION['cart'][$productId]);
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
            flash('error', 'Selecione um produto disponível e uma quantidade válida.');
            redirect('/sales/new');
        }

        $cart = $_SESSION['cart'] ?? [];
        $newQuantity = ($cart[$productId] ?? 0) + $quantity;
        if ($newQuantity > (int) $product['stock']) {
            flash('error', 'A quantidade solicitada excede o estoque disponível.');
            redirect('/sales/new');
        }

        $cart[$productId] = $newQuantity;
        $_SESSION['cart'] = $cart;
        flash('success', 'Produto adicionado ao carrinho.');
        redirect('/sales/new');
    }

    public function updateItem(string $id): void
    {
        $product = $this->products->find((int) $id);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if (!$product || !$quantity || $quantity < 1 || $quantity > (int) $product['stock']) {
            flash('error', 'Informe uma quantidade válida dentro do estoque disponível.');
            redirect('/sales/new');
        }

        $_SESSION['cart'][(int) $id] = $quantity;
        flash('success', 'Quantidade atualizada.');
        redirect('/sales/new');
    }

    public function removeItem(string $id): void
    {
        unset($_SESSION['cart'][(int) $id]);
        flash('success', 'Produto removido do carrinho.');
        redirect('/sales/new');
    }

    public function clearCart(): void
    {
        unset($_SESSION['cart']);
        flash('success', 'Carrinho esvaziado.');
        redirect('/sales/new');
    }

    public function checkout(): void
    {
        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT) ?: null;
        try {
            $saleId = $this->saleService->checkout(
                $_SESSION['cart'] ?? [],
                $customerId,
                (string) ($_POST['payment_method'] ?? '')
            );
            unset($_SESSION['cart']);
            flash('success', 'Venda #' . $saleId . ' finalizada com sucesso.');
            redirect('/sales/' . $saleId);
        } catch (\DomainException $exception) {
            flash('error', $exception->getMessage());
            redirect('/sales/new');
        }
    }

    public function detail(string $id): void
    {
        $sale = $this->sales->findWithItems((int) $id);
        if (!$sale) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Venda não encontrada']);
            return;
        }

        View::render('sales/detail', ['title' => 'Venda #' . $id, 'sale' => $sale]);
    }

    public function reports(): void
    {
        $filters = $this->filters();
        View::render('reports/index', [
            'title' => 'Relatórios de vendas',
            'filters' => $filters,
            'sales' => $this->sales->search($filters),
            'totals' => $this->sales->totals($filters),
        ]);
    }

    public function export(): void
    {
        $sales = $this->sales->search($this->filters());
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio-vendas.csv"');
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \RuntimeException('Não foi possível gerar o relatório.');
        }
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Código', 'Data', 'Cliente', 'Pagamento', 'Status', 'Total'], ';', '"', '');
        foreach ($sales as $sale) {
            fputcsv($output, [
                (int) $sale['id'],
                $sale['created_at'],
                self::spreadsheetSafe((string) ($sale['customer_name'] ?? 'Consumidor final')),
                self::spreadsheetSafe((string) $sale['payment_method']),
                $sale['status'] === 'completed' ? 'Concluída' : 'Cancelada',
                number_format((float) $sale['total'], 2, ',', '.'),
            ], ';', '"', '');
        }
        fclose($output);
    }

    public function cancel(string $id): void
    {
        try {
            $this->saleService->cancel((int) $id);
            flash('success', 'Venda cancelada e estoque restaurado.');
        } catch (\DomainException $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/reports');
    }

    private function filters(): array
    {
        $from = trim((string) ($_GET['from'] ?? ''));
        $to = trim((string) ($_GET['to'] ?? ''));
        $status = (string) ($_GET['status'] ?? '');
        return [
            'from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : '',
            'to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : '',
            'status' => in_array($status, ['completed', 'cancelled'], true) ? $status : '',
        ];
    }

    private static function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}
