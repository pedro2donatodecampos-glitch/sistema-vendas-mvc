<div class="page-heading">
    <div><p class="eyebrow">RELACIONAMENTO</p><h1><?= e($title) ?></h1><p class="text-muted mb-0">Os campos com * são obrigatórios.</p></div>
    <a class="btn btn-light" href="/customers">Voltar</a>
</div>
<section class="panel form-panel">
    <form method="post" action="<?= $customer ? '/customers/' . (int) $customer['id'] . '/update' : '/customers' ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nome completo *</label>
                <input class="form-control" id="name" name="name" value="<?= e($customer['name'] ?? '') ?>" maxlength="120" required autofocus>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e($customer['email'] ?? '') ?>" maxlength="180">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Telefone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e($customer['phone'] ?? '') ?>" maxlength="30" placeholder="(00) 00000-0000">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="document">CPF</label>
                <input class="form-control" id="document" name="document" value="<?= e($customer['document'] ?? '') ?>" maxlength="20" placeholder="000.000.000-00">
            </div>
        </div>
        <div class="form-actions"><a class="btn btn-light" href="/customers">Cancelar</a><button class="btn btn-primary" type="submit">Salvar cliente</button></div>
    </form>
</section>
