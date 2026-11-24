<?php
/**
 * index.php — ReFindUHO
 * Halaman beranda (Semua aktor & publik).
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/controllers/LaporanController.php';

$pageTitle = 'Beranda';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$user       = current_user();
$isLoggedIn = is_logged_in();
$role       = $user['role'] ?? '';

// Ambil laporan terbaru yang telah disetujui untuk ditampilkan di beranda
$laporanController = new LaporanController($pdo);
$laporanTerbaru = $laporanController->laporanTerbaru(6);
?>

<!-- Hero Section -->
<section class="hero-section py-5">
    <div class="container container-rf py-4 text-center">
        <span class="badge bg-warning text-dark font-mono px-3 py-2 rounded-pill fw-semibold mb-3">
            <i class="bi bi-mortarboard-fill me-1"></i> Universitas Halu Oleo
        </span>
        <h1 class="display-6 fw-bold mb-3">Kehilangan atau Menemukan Barang di Kampus?</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 640px; opacity: 0.95;">
            ReFindUHO membantu seluruh civitas akademika Universitas Halu Oleo melaporkan, menelusuri, dan mengembalikan barang hilang maupun temuan di lingkungan kampus secara aman dan terverifikasi.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-light text-rf-primary fw-semibold px-4">
                <i class="bi bi-grid"></i> Lihat Daftar Barang
            </a>
            <?php if ($isLoggedIn && $role === 'mahasiswa'): ?>
                <a href="<?= base_url('pages/mahasiswa/form_laporan.php') ?>" class="btn btn-warning text-dark fw-semibold px-4">
                    <i class="bi bi-plus-circle"></i> Buat Laporan Baru
                </a>
                <a href="<?= base_url('pages/mahasiswa/dashboard.php') ?>" class="btn btn-outline-light px-4">
                    <i class="bi bi-layout-text-sidebar"></i> Laporan Saya
                </a>
            <?php elseif ($isLoggedIn && $role === 'admin'): ?>
                <a href="<?= base_url('pages/admin/dashboard.php') ?>" class="btn btn-warning text-dark fw-semibold px-4">
                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                </a>
            <?php else: ?>
                <a href="<?= base_url('pages/auth/register.php') ?>" class="btn btn-warning text-dark fw-semibold px-4">
                    <i class="bi bi-person-plus"></i> Daftar Akun Mahasiswa
                </a>
                <a href="<?= base_url('pages/auth/login.php') ?>" class="btn btn-outline-light px-4">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Latest Approved Items Preview (Jika ada) -->
<?php if (!empty($laporanTerbaru)): ?>
<section class="py-5 bg-white border-bottom">
    <div class="container container-rf">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="h5 fw-bold mb-1">Barang Terbaru yang Dilaporkan</h2>
                <p class="text-muted small mb-0">Laporan barang hilang dan temuan terbaru yang telah diverifikasi admin</p>
            </div>
            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-sm btn-outline-rf-primary">
                Lihat Semua Barang <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach ($laporanTerbaru as $item): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-interactive h-100 d-flex flex-column">
                        <div class="img-shimmer-wrap skeleton-shimmer" style="border-radius: 8px 8px 0 0;">
                            <?php
                            $fotoAda = !empty($item['foto']) && file_exists(UPLOAD_DIR . $item['foto']);
                            $fotoSrc = $fotoAda ? base_url('uploads/' . $item['foto']) : base_url('assets/images/no-image.svg');
                            ?>
                            <img src="<?= escape($fotoSrc) ?>" alt="<?= escape($item['nama_barang']) ?>"
                                 onerror="this.src='<?= base_url('assets/images/no-image.svg') ?>'">
                        </div>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <?php if ($item['jenis_laporan'] === 'hilang'): ?>
                                <span class="badge-rf badge-jenis-hilang mb-2 align-self-start">
                                    <i class="bi bi-exclamation-circle"></i> Barang Hilang
                                </span>
                            <?php else: ?>
                                <span class="badge-rf badge-jenis-temuan mb-2 align-self-start">
                                    <i class="bi bi-hand-index"></i> Barang Temuan
                                </span>
                            <?php endif; ?>

                            <h3 class="h6 fw-bold mb-1 text-clamp-2"><?= escape($item['nama_barang']) ?></h3>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-tag"></i> <?= escape($item['nama_kategori']) ?>
                            </p>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-geo-alt"></i> <?= escape($item['lokasi']) ?>
                            </p>
                            <p class="small text-muted mb-3 font-mono">
                                <i class="bi bi-calendar3"></i> <?= escape($item['tanggal_kejadian']) ?>
                            </p>

                            <div class="mt-auto">
                                <a href="<?= base_url('pages/mahasiswa/detail_barang.php?id=' . (int) $item['id_barang']) ?>"
                                   class="btn btn-sm btn-outline-rf-primary w-100">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 3-Step How It Works -->
<section class="py-5">
    <div class="container container-rf">
        <h2 class="h5 fw-bold text-center mb-4">Cara Kerja ReFindUHO</h2>
        <div class="row g-3 justify-content-center">
            <?php
            $steps = [
                ['icon' => 'bi-pencil-square', 'title' => 'Lapor', 'desc' => 'Mahasiswa membuat laporan barang hilang atau temuan dengan mengisi formulir lengkap beserta foto.'],
                ['icon' => 'bi-shield-check', 'title' => 'Verifikasi Admin', 'desc' => 'Admin kampus memeriksa dan memverifikasi laporan sebelum ditampilkan di daftar barang publik.'],
                ['icon' => 'bi-telephone-outbound', 'title' => 'Hubungi & Selesaikan', 'desc' => 'Pengguna yang telah masuk dapat melihat kontak pelapor untuk berkoordinasi dan menyelesaikan proses serah terima.'],
            ];
            foreach ($steps as $i => $step):
            ?>
                <div class="col-12 col-md-4">
                    <div class="card-interactive p-4 h-100 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                             style="width: 52px; height: 52px; background-color: var(--rf-primary-soft);">
                            <i class="bi <?= $step['icon'] ?>" style="font-size: 1.35rem; color: var(--rf-primary);"></i>
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
            <h2 class="h6 fw-bold mb-2"><i class="bi bi-headset me-1 text-rf-primary"></i>Layanan Administrasi Kampus</h2>
            <p class="text-muted small mb-2">
                Hubungi administrator kampus jika memerlukan bantuan khusus terkait barang hilang atau kendala teknis.
            </p>
            <p class="fw-medium mb-0 font-mono small">
                <i class="bi bi-envelope me-1 text-rf-primary"></i>admin@uho.ac.id
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
