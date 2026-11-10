<div class="page-heading">
    <div>
        <p class="eyebrow">RELACIONAMENTO</p>
        <h1>Clientes</h1>
        <p class="text-muted mb-0">Cadastre e mantenha os dados de seus clientes.</p>
    </div>
    <a class="btn btn-primary" href="/customers/create">+ Novo cliente</a>
</div>
<section class="panel">
    <?php if ($customers === []): ?>
        <div class="empty-state"><span class="empty-icon">♙</span><h2>Nenhum cliente cadastrado</h2><p>Adicione o primeiro cliente para começar.</p><a class="btn btn-outline-primary" href="/customers/create">Cadastrar cliente</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle app-table mb-0">
                <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>CPF</th><th class="text-end">Ações</th></tr></thead>
                <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($customer['name']) ?></td>
                        <td><?= e($customer['email'] ?: '—') ?></td>
                        <td><?= e($customer['phone'] ?: '—') ?></td>
                        <td><?= e($customer['document'] ?: '—') ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-light" href="/customers/<?= (int) $customer['id'] ?>/edit">Editar</a>
                            <form class="d-inline" method="post" action="/customers/<?= (int) $customer['id'] ?>/delete" onsubmit="return confirm('Remover este cliente?')">
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
