<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\SaleRepository;
use App\Services\PaymentMethodFactory;
use App\Services\SaleService;

/**
 * Controller da tela "Relatórios de vendas":
 * Read = listar/filtrar/detalhar/exportar, Update = cancelar venda,
 * Delete = excluir do histórico uma venda cancelada.
 * (O Create de uma venda acontece ao finalizar o carrinho.)
 */
final class ReportController
{
    private SaleRepository $sales;
    private SaleService $saleService;

    public function __construct()
    {
        $db = Database::connection();
        $this->sales = new SaleRepository($db);
        $this->saleService = new SaleService($db);
    }

    public function index(): void
    {
        $filters = $this->filters();
        View::render('reports/index', [
            'title' => 'Relatórios de vendas',
            'filters' => $filters,
            'sales' => $this->sales->search($filters),
            'totals' => $this->sales->totals($filters),
        ]);
    }

    public function show(string $id): void
    {
        $sale = $this->sales->findWithItems((int) $id);
        if (!$sale) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Venda não encontrada']);
            return;
        }

        try {
            $receiptNote = PaymentMethodFactory::create($sale['payment_method'])->receiptNote();
        } catch (\DomainException) {
            $receiptNote = '';
        }

        View::render('sales/detail', ['title' => 'Venda #' . $id, 'sale' => $sale, 'receiptNote' => $receiptNote]);
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

    public function delete(string $id): void
    {
        if ($this->sales->deleteCancelled((int) $id)) {
            flash('success', 'Venda #' . (int) $id . ' excluída do histórico.');
        } else {
            flash('error', 'Somente vendas canceladas podem ser excluídas.');
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
