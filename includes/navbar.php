<?php
/**
 * includes/navbar.php — ReFindUHO
 * Navigasi responsif, menu dinamis berbasis $_SESSION['role'],
 * tombol login/logout, dan alert notifikasi get_flash().
 */

$user = current_user();
$isLoggedIn = is_logged_in();
$role = $user['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-rf-primary sticky-top">
    <div class="container container-rf">
        <a class="navbar-brand fw-bold" href="<?= base_url('index.php') ?>">
            <i class="bi bi-search-heart me-1"></i>ReFindUHO
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php') ?>">
                        <i class="bi bi-house-door me-1"></i>Beranda
                    </a>
                </li>

                <?php if ($isLoggedIn && $role === 'mahasiswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>">
                            <i class="bi bi-grid me-1"></i>Daftar Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('pages/mahasiswa/form_laporan.php') ?>">
                            <i class="bi bi-plus-circle me-1"></i>Buat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('pages/mahasiswa/dashboard.php') ?>">
                            <i class="bi bi-layout-text-sidebar me-1"></i>Laporan Saya
                        </a>
                    </li>
                <?php elseif ($isLoggedIn && $role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('pages/admin/dashboard.php') ?>">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard Admin
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= escape($user['nama_lengkap'] ?? '') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= escape($user['email'] ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('pages/auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-1"></i>Keluar
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('pages/auth/login.php') ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-light text-rf-primary fw-semibold ms-lg-2 mt-2 mt-lg-0" href="<?= base_url('pages/auth/register.php') ?>">
                            Daftar Akun
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php
// Flash messages
$flashMessages = get_flash();
if (!empty($flashMessages)): ?>
<div class="container container-rf mt-3">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= escape($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= escape($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
