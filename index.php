<?php
/**
 * index.php — ReFindUHO
 * Halaman beranda (Semua aktor).
 */

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Beranda';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$user = current_user();
$isLoggedIn = is_logged_in();
$role = $user['role'] ?? '';
?>

<!-- Hero Section -->
<section class="hero-section py-5">
    <div class="container container-rf py-4 text-center">
        <h1 class="display-6 fw-bold mb-3">Kehilangan atau Menemukan Barang di Kampus?</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 600px; opacity: 0.9;">
            ReFindUHO membantu civitas akademika Universitas Halu Oleo melaporkan dan menelusuri barang hilang maupun temuan di lingkungan kampus.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php if ($isLoggedIn && $role === 'mahasiswa'): ?>
                <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-light text-rf-primary fw-semibold px-4">
                    <i class="bi bi-grid"></i> Lihat Daftar Barang
                </a>
                <a href="<?= base_url('pages/mahasiswa/dashboard.php') ?>" class="btn btn-outline-light px-4">
                    <i class="bi bi-layout-text-sidebar"></i> Laporan Saya
                </a>
            <?php elseif ($isLoggedIn && $role === 'admin'): ?>
                <a href="<?= base_url('pages/admin/dashboard.php') ?>" class="btn btn-light text-rf-primary fw-semibold px-4">
                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                </a>
            <?php else: ?>
                <a href="<?= base_url('pages/auth/register.php') ?>" class="btn btn-light text-rf-primary fw-semibold px-4">
                    <i class="bi bi-person-plus"></i> Daftar Akun
                </a>
                <a href="<?= base_url('pages/auth/login.php') ?>" class="btn btn-outline-light px-4">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3-Step How It Works -->
<section class="py-5">
    <div class="container container-rf">
        <h2 class="h5 fw-bold text-center mb-4">Cara Kerja ReFindUHO</h2>
        <div class="row g-3 justify-content-center">
            <?php
            $steps = [
                ['icon' => 'bi-pencil-square', 'title' => 'Lapor', 'desc' => 'Mahasiswa membuat laporan barang hilang atau temuan dengan mengisi formulir lengkap beserta foto.'],
                ['icon' => 'bi-shield-check', 'title' => 'Verifikasi Admin', 'desc' => 'Admin kampus memeriksa dan memverifikasi laporan sebelum ditampilkan di daftar barang.'],
                ['icon' => 'bi-telephone-outbound', 'title' => 'Hubungi & Selesaikan', 'desc' => 'Pengguna menghubungi pelapor melalui kontak yang tersedia dan menyelesaikan proses.'],
            ];
            foreach ($steps as $i => $step):
            ?>
                <div class="col-12 col-md-4">
                    <div class="card-interactive p-4 h-100 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                             style="width: 48px; height: 48px; background-color: var(--rf-primary-soft);">
                            <i class="bi <?= $step['icon'] ?>" style="font-size: 1.25rem; color: var(--rf-primary);"></i>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <span class="fw-bold font-mono small" style="color: var(--rf-accent);">0<?= $i + 1 ?></span>
                            <h3 class="h6 fw-bold mb-0"><?= $step['title'] ?></h3>
                        </div>
                        <p class="text-muted small mb-0"><?= $step['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Card -->
<section class="pb-5">
    <div class="container container-rf">
        <div class="card-surface p-4 text-center mx-auto" style="max-width: 540px;">
            <h2 class="h6 fw-bold mb-2"><i class="bi bi-headset me-1"></i>Layanan Administrasi</h2>
            <p class="text-muted small mb-2">
                Hubungi administrator kampus jika memerlukan bantuan terkait laporan barang.
            </p>
            <p class="fw-medium mb-0">
                <i class="bi bi-envelope me-1"></i>admin@uho.ac.id
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
