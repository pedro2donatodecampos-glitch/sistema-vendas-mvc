<div class="page-heading">
    <div><p class="eyebrow">ESTOQUE</p><h1><?= e($title) ?></h1><p class="text-muted mb-0">Os campos com * são obrigatórios.</p></div>
    <a class="btn btn-light" href="/products">Voltar</a>
</div>
<section class="panel form-panel">
    <form method="post" enctype="multipart/form-data" action="<?= $product ? '/products/' . (int) $product['id'] . '/update' : '/products' ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="code">COD Produto *</label>
                <input class="form-control" id="code" name="code" value="<?= e($product['code'] ?? '') ?>" maxlength="30" required autofocus>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="name">Nome Produto *</label>
                <input class="form-control" id="name" name="name" value="<?= e($product['name'] ?? '') ?>" maxlength="120" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="category">Categoria *</label>
                <input class="form-control" id="category" name="category" value="<?= e($product['category'] ?? '') ?>" maxlength="60" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="expires_at">Validade *</label>
                <input class="form-control" type="date" id="expires_at" name="expires_at" value="<?= e($product['expires_at'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="stock">Quantidade *</label>
                <input class="form-control" type="number" min="0" step="1" id="stock" name="stock" value="<?= e($product['stock'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="unit_price">Preço Unitário *</label>
                <input class="form-control" type="number" min="0" step="0.01" id="unit_price" name="unit_price" value="<?= e($product['unit_price'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="package_price">Preço Pacote *</label>
                <input class="form-control" type="number" min="0" step="0.01" id="package_price" name="package_price" value="<?= e($product['package_price'] ?? '') ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="image">Imagem do Produto <?= $product ? '' : '*' ?></label>
                <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($product['image'])): ?><img class="product-thumb product-image large" src="<?= e($product['image']) ?>" alt="Imagem atual"><?php endif; ?>
                    <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $product ? '' : 'required' ?>>
                </div>
                <div class="form-text"><?= $product ? 'Envie uma nova imagem somente se quiser substituir a atual. ' : '' ?>JPG, PNG, WEBP ou GIF de até 2 MB.</div>
            </div>
        </div>
        <div class="form-actions"><a class="btn btn-light" href="/products">Cancelar</a><button class="btn btn-success" type="submit"><?= $product ? 'Salvar produto' : 'Cadastrar' ?></button></div>
    </form>
</section>
