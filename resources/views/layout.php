<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Vitrine - Sistema de Vendas') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="/"><span class="brand-mark">V</span> Vitrine <small>PDV</small></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Abrir navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/sales/new">Carrinho</a>
                <a class="nav-link" href="/customers">Clientes</a>
                <a class="nav-link" href="/products">Produtos</a>
                <a class="nav-link" href="/reports">Relatórios</a>
            </div>
        </div>
    </div>
</nav>
<main class="container app-main">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= e($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php require $viewFile; ?>
</main>
<footer class="app-footer">
    <div class="container d-flex justify-content-between flex-wrap gap-2">
        <span>Vitrine PDV <span class="text-muted">· Gestão de vendas</span></span>
        <span class="text-muted">Projeto acadêmico MVC</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
