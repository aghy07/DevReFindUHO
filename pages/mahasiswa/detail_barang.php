<?php
/**
 * pages/mahasiswa/detail_barang.php — ReFindUHO
 * Halaman detail barang (FR-06, BR-08).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$authController    = new AuthController($pdo);
$authController->pastikanLogin();

$laporanController = new LaporanController($pdo);

$id = (int) ($_GET['id'] ?? 0);
$barang = $laporanController->detailBarang($id);

if ($barang === null) {
    set_flash('warning', 'Laporan barang tidak ditemukan atau belum disetujui.');
    header('Location: ' . base_url('pages/mahasiswa/daftar_barang.php'));
    exit;
}

$pageTitle = $barang->nama_barang;
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-4">
    <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>

    <div class="row g-4">
        <!-- Photo Column -->
        <div class="col-12 col-lg-5">
            <div class="card-surface overflow-hidden">
                <div class="img-shimmer-wrap skeleton-shimmer">
                    <?php
                    $fotoSrc = !empty($barang->foto)
                        ? base_url('uploads/' . $barang->foto)
                        : base_url('assets/images/no-image.svg');
                    ?>
                    <img src="<?= escape($fotoSrc) ?>" alt="<?= escape($barang->nama_barang) ?>"
                         onerror="this.src='<?= base_url('assets/images/no-image.svg') ?>'">
                </div>
            </div>
        </div>

        <!-- Detail Column -->
        <div class="col-12 col-lg-7">
            <div class="card-surface p-4">
                <!-- Jenis Badge -->
                <?php if ($barang->jenis_laporan === 'hilang'): ?>
                    <span class="badge-rf badge-jenis-hilang mb-3">
                        <i class="bi bi-exclamation-circle"></i> Barang Hilang
                    </span>
                <?php else: ?>
                    <span class="badge-rf badge-jenis-temuan mb-3">
                        <i class="bi bi-hand-index"></i> Barang Temuan
                    </span>
                <?php endif; ?>

                <h1 class="h4 fw-bold mb-3"><?= escape($barang->nama_barang) ?></h1>

                <table class="table table-borderless mb-3">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 160px;"><i class="bi bi-tag me-1"></i>Kategori</td>
                            <td class="fw-medium"><?= escape($barang->nama_kategori) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-geo-alt me-1"></i>Lokasi</td>
                            <td class="fw-medium"><?= escape($barang->lokasi) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-calendar3 me-1"></i>Tanggal</td>
                            <td class="fw-medium font-mono"><?= escape($barang->tanggal_kejadian) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-person me-1"></i>Pelapor</td>
                            <td class="fw-medium"><?= escape($barang->nama_pelapor) ?></td>
                        </tr>
                    </tbody>
                </table>

                <h2 class="h6 fw-bold mb-2">Deskripsi</h2>
                <p class="mb-4" style="white-space: pre-line;"><?= escape($barang->deskripsi) ?></p>

                <!-- Contact Card (BR-08: hanya untuk pengguna login) -->
                <?php if (is_logged_in()): ?>
                    <div class="card-surface p-3" style="background-color: var(--rf-primary-soft);">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-telephone me-1"></i>Kontak Pelapor</h3>
                        <p class="mb-2 fw-medium"><?= escape($barang->kontak) ?></p>
                        <button type="button" class="btn btn-sm btn-rf-primary"
                                data-copy-text="<?= escape($barang->kontak) ?>">
                            <i class="bi bi-clipboard"></i> Salin Kontak
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
