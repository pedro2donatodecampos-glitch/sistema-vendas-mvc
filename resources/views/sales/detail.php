<div class="page-heading">
    <div><p class="eyebrow">PONTO DE VENDA</p><h1>Venda #<?= (int) $sale['id'] ?></h1><p class="text-muted mb-0">Comprovante da venda realizada.</p></div>
    <a class="btn btn-light" href="/reports">Voltar aos relatórios</a>
</div>
<section class="panel">
    <div class="sale-summary">
        <div><span>Data</span><strong><?= e(date('d/m/Y H:i', strtotime($sale['created_at']))) ?></strong></div>
        <div><span>Cliente</span><strong><?= e($sale['customer_name'] ?? 'Consumidor final') ?></strong></div>
        <div><span>Pagamento</span><strong><?= e($sale['payment_method']) ?></strong><?php if ($receiptNote !== ''): ?><small class="text-muted"><?= e($receiptNote) ?></small><?php endif; ?></div>
        <div><span>Status</span><strong><?= $sale['status'] === 'completed' ? 'Concluída' : 'Cancelada' ?></strong></div>
    </div>
    <div class="table-responsive mt-4">
        <table class="table align-middle app-table">
            <thead><tr><th>Produto</th><th>Quantidade</th><th>Preço unitário</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody><?php foreach ($sale['items'] as $item): ?><tr><td class="fw-semibold"><?= e($item['product_name']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= money($item['unit_price']) ?></td><td class="text-end"><?= money((float) $item['quantity'] * (float) $item['unit_price']) ?></td></tr><?php endforeach; ?></tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end fs-5"><?= money($sale['total']) ?></th></tr></tfoot>
        </table>
    </div>
</section>
