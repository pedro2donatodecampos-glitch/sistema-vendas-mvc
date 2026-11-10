<div class="page-heading">
    <div><p class="eyebrow">ANÁLISE</p><h1>Relatórios de vendas</h1><p class="text-muted mb-0">Consulte o histórico e acompanhe os resultados.</p></div>
    <a class="btn btn-primary" href="/reports/export?<?= e(http_build_query($filters)) ?>">↓ Exportar CSV</a>
</div>
<section class="panel filter-panel">
    <form class="row g-3 align-items-end" method="get" action="/reports">
        <div class="col-sm-4 col-lg-3"><label class="form-label" for="from">De</label><input class="form-control" type="date" id="from" name="from" value="<?= e($filters['from']) ?>"></div>
        <div class="col-sm-4 col-lg-3"><label class="form-label" for="to">Até</label><input class="form-control" type="date" id="to" name="to" value="<?= e($filters['to']) ?>"></div>
        <div class="col-sm-4 col-lg-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">Todos</option><option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Concluídas</option><option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Canceladas</option></select></div>
        <div class="col-lg-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Filtrar</button><a class="btn btn-light" href="/reports">Limpar</a></div>
    </form>
</section>
<div class="row g-3 summary-cards">
    <div class="col-md-6"><section class="summary-card"><span>Vendas concluídas</span><strong><?= (int) $totals['count'] ?></strong><small>No período selecionado</small></section></div>
    <div class="col-md-6"><section class="summary-card accent"><span>Faturamento</span><strong><?= money($totals['revenue']) ?></strong><small>Considera somente vendas concluídas</small></section></div>
</div>
<section class="panel">
    <div class="panel-heading"><div><h2>Histórico de vendas</h2><p><?= count($sales) ?> registro(s) encontrado(s)</p></div></div>
    <?php if ($sales === []): ?>
        <div class="empty-state compact"><h2>Nenhuma venda encontrada</h2><p>Ajuste os filtros ou faça uma nova venda.</p><a href="/sales/new">Ir para o carrinho</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle app-table mb-0">
                <thead><tr><th>Venda</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th>Status</th><th class="text-end">Total</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int) $sale['id'] ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($sale['created_at']))) ?></td>
                        <td><?= e($sale['customer_name'] ?? 'Consumidor final') ?></td>
                        <td><?= e($sale['payment_method']) ?></td>
                        <td><span class="badge rounded-pill <?= $sale['status'] === 'completed' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $sale['status'] === 'completed' ? 'Concluída' : 'Cancelada' ?></span></td>
                        <td class="text-end fw-semibold"><?= money($sale['total']) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-light" href="/sales/<?= (int) $sale['id'] ?>">Detalhes</a>
                            <?php if ($sale['status'] === 'completed'): ?>
                                <form class="d-inline" method="post" action="/sales/<?= (int) $sale['id'] ?>/cancel" onsubmit="return confirm('Cancelar a venda e devolver os itens ao estoque?')">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
                                </form>
                            <?php else: ?>
                                <form class="d-inline" method="post" action="/sales/<?= (int) $sale['id'] ?>/delete" onsubmit="return confirm('Excluir definitivamente esta venda cancelada do histórico?')">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
