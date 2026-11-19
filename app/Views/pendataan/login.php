<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Pendataan Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-body-tertiary d-flex align-items-center min-vh-100">
<div class="container" style="max-width: 420px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-boxes-stacked fa-2x text-success"></i>
                <h1 class="h4 fw-bold mt-2 mb-0">PENDATAAN BARANG</h1>
                <div class="text-secondary small">Masuk untuk melanjutkan</div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('pendataan/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= esc(old('username')) ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> MASUK
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
