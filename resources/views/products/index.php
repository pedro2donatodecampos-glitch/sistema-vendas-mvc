<div class="page-heading">
    <div>
        <p class="eyebrow">ESTOQUE</p>
        <h1>Produtos</h1>
        <p class="text-muted mb-0">Cadastre os produtos que serão vendidos no carrinho.</p>
    </div>
    <a class="btn btn-primary" href="/products/create">+ Novo produto</a>
</div>
<section class="panel">
    <?php if ($products === []): ?>
        <div class="empty-state"><span class="empty-icon">▣</span><h2>Nenhum produto cadastrado</h2><p>Cadastre o primeiro produto para começar a vender.</p><a class="btn btn-outline-primary" href="/products/create">Cadastrar produto</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle app-table mb-0">
                <thead><tr><th>Produto</th><th>Código</th><th>Categoria</th><th>Validade</th><th>Estoque</th><th class="text-end">Preço unit.</th><th class="text-end">Preço pacote</th><th class="text-end">Ações</th></tr></thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <?php $expired = \App\Models\Product::isExpired($product['expires_at']); ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($product['image']): ?><img class="product-thumb product-image" src="<?= e($product['image']) ?>" alt=""><?php else: ?><div class="product-thumb"><?= e(mb_substr($product['name'], 0, 1)) ?></div><?php endif; ?>
                                <span class="fw-semibold"><?= e($product['name']) ?></span>
                            </div>
                        </td>
                        <td><?= e($product['code']) ?></td>
                        <td><?= e($product['category']) ?></td>
                        <td>
                            <?= $product['expires_at'] ? e(date('d/m/Y', strtotime($product['expires_at']))) : '—' ?>
                            <?php if ($expired): ?><span class="badge rounded-pill text-bg-danger ms-1">Vencido</span><?php endif; ?>
                        </td>
                        <td><?= (int) $product['stock'] ?></td>
                        <td class="text-end"><?= money($product['unit_price']) ?></td>
                        <td class="text-end"><?= (float) $product['package_price'] > 0 ? money($product['package_price']) : '—' ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-light" href="/products/<?= (int) $product['id'] ?>/edit">Editar</a>
                            <form class="d-inline" method="post" action="/products/<?= (int) $product['id'] ?>/delete" onsubmit="return confirm('Remover este produto?')">
                                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
