<div class="page-heading">
    <div><p class="eyebrow">PONTO DE VENDA</p><h1>Nova venda</h1><p class="text-muted mb-0">Adicione produtos ao carrinho e finalize a compra.</p></div>
    <span class="status-chip"><span></span> Caixa aberto</span>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-heading"><div><h2>Produtos disponíveis</h2><p>Selecione um produto para adicionar.</p></div></div>
            <?php if ($products === []): ?>
                <div class="empty-state compact"><h2>Sem produtos em estoque</h2><p>Cadastre ou reponha produtos para iniciar uma venda.</p><a class="btn btn-outline-primary" href="/products">Ir para produtos</a></div>
            <?php else: ?>
                <div class="product-list">
                <?php foreach ($products as $product): ?>
                    <?php $expired = \App\Models\Product::isExpired($product['expires_at']); ?>
                    <div class="product-row">
                        <?php if ($product['image']): ?><img class="product-thumb product-image" src="<?= e($product['image']) ?>" alt=""><?php else: ?><div class="product-thumb"><?= e(mb_substr($product['name'], 0, 1)) ?></div><?php endif; ?>
                        <div class="product-info"><strong><?= e($product['name']) ?></strong><span><?= e($product['category']) ?> · <?= (int) $product['stock'] ?> disponíveis<?php if ($expired): ?> · <b class="text-danger">Vencido</b><?php endif; ?></span></div>
                        <div class="product-price"><?= money($product['unit_price']) ?></div>
                        <form class="add-product-form" method="post" action="/cart/items">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input class="form-control form-control-sm quantity-input" type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" aria-label="Quantidade">
                            <button class="btn btn-sm btn-primary" type="submit" aria-label="Adicionar <?= e($product['name']) ?>" <?= $expired ? 'disabled' : '' ?>>+</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <div class="col-lg-5">
        <section class="panel cart-panel">
            <div class="panel-heading"><div><h2>Seu carrinho</h2><p><?= count($items) ?> <?= count($items) === 1 ? 'produto' : 'produtos' ?></p></div>
                <?php if ($items !== []): ?><form method="post" action="/cart/clear" onsubmit="return confirm('Esvaziar o carrinho?')"><?= csrf_field() ?><button class="btn btn-sm btn-link text-danger" type="submit">Esvaziar</button></form><?php endif; ?>
            </div>
            <?php if ($items === []): ?>
                <div class="empty-state compact"><span class="empty-icon">🛍</span><h2>Carrinho vazio</h2><p>Adicione produtos para continuar.</p></div>
            <?php else: ?>
                <div class="cart-items">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-top"><div><strong><?= e($item['name']) ?></strong><span><?= money($item['unit_price']) ?> cada</span></div>
                            <form method="post" action="/cart/items/<?= (int) $item['id'] ?>/remove"><?= csrf_field() ?><button class="icon-button" type="submit" aria-label="Remover <?= e($item['name']) ?>">×</button></form>
                        </div>
                        <div class="cart-item-bottom">
                            <form class="d-flex gap-2" method="post" action="/cart/items/<?= (int) $item['id'] ?>">
                                <?= csrf_field() ?><input class="form-control form-control-sm quantity-input" type="number" name="quantity" value="<?= (int) $item['cart_quantity'] ?>" min="1" max="<?= (int) $item['stock'] ?>" aria-label="Quantidade de <?= e($item['name']) ?>">
                                <button class="btn btn-sm btn-light" type="submit">Atualizar</button>
                            </form>
                            <strong><?= money($item['line_total']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <div class="cart-total"><span>Total</span><strong><?= money($total) ?></strong></div>
                <form method="post" action="/sales/checkout">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="customer_id">Cliente</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">Consumidor final</option>
                            <?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>"><?= e($customer['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="payment_method">Forma de pagamento</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Selecione</option>
                            <?php foreach ($paymentMethods as $method): ?><option value="<?= e($method) ?>"><?= e($method) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-lg w-100" type="submit">Finalizar venda · <?= money($total) ?></button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>
