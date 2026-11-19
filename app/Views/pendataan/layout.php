<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Pendataan Barang') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand bg-success navbar-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url('pendataan') ?>">
            <i class="fa-solid fa-boxes-stacked me-2"></i>PENDATAAN BARANG
        </a>
        <div class="d-flex align-items-center gap-3 text-white">
            <span class="small"><?= esc(session()->get('full_name')) ?> · <?= esc(session()->get('role')) ?></span>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light fw-bold">
                <i class="fa-solid fa-right-from-bracket me-1"></i> KELUAR
            </a>
        </div>
    </div>
</nav>
<main class="container-fluid py-4">
    <?= $this->renderSection('content') ?>
</main>
</body>
</html>
