<?php
/**
 * pages/mahasiswa/detail_barang.php — ReFindUHO
 * Halaman detail barang (FR-06, BR-08).
 * Dapat diakses publik; informasi kontak dibatasi untuk pengguna yang telah login (BR-08).
 * Pemilik laporan dapat melihat status laporannya dan menandai selesai jika sudah kembali.
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$authController    = new AuthController($pdo);
$laporanController = new LaporanController($pdo);

$user         = current_user();
$userId       = $user['id_user'] ?? null;
$isAdmin      = ($user['role'] ?? '') === 'admin';
$id           = (int) ($_GET['id'] ?? 0);

// Cari barang (publik: hanya Disetujui; pemilik / admin: semua status)
$barang = $laporanController->detailBarang($id, $userId, $isAdmin);

if ($barang === null) {
    set_flash('warning', 'Laporan barang tidak ditemukan atau belum disetujui oleh admin.');
    header('Location: ' . base_url('pages/mahasiswa/daftar_barang.php'));
    exit;
}

$isOwner = ($userId !== null && $barang->dimilikiOleh($userId));

// Handle penyelesaian mandiri oleh pemilik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Sesi tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.');
    } else {
        $aksi = $_POST['aksi'] ?? '';
        if ($aksi === 'selesaikan_mandiri' && $isOwner) {
            $hasil = $laporanController->selesaikanLaporanMahasiswa($id, $userId);
            set_flash($hasil['success'] ? 'success' : 'danger', $hasil['message']);
            header('Location: ' . base_url('pages/mahasiswa/detail_barang.php?id=' . $id));
            exit;
        }
    }
}

$pageTitle = $barang->nama_barang;
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-4">
    <div class="mb-3 d-flex align-items-center gap-2">
        <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
        <?php if ($isOwner): ?>
            <a href="<?= base_url('pages/mahasiswa/dashboard.php') ?>" class="btn btn-sm btn-outline-rf-primary">
                <i class="bi bi-layout-text-sidebar"></i> Laporan Saya
            </a>
        <?php endif; ?>
    </div>

    <!-- Banner kepemilikan laporan -->
    <?php if ($isOwner): ?>
        <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <i class="bi bi-person-check-fill me-1"></i>
                <strong>Ini adalah laporan Anda.</strong> Status saat ini:
                <span class="badge-rf <?= badge_status_class($barang->status) ?>"><?= escape($barang->status) ?></span>
            </div>
            <?php if ($barang->status === 'Disetujui'): ?>
                <form method="POST" action="" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="selesaikan_mandiri">
                    <button type="submit" class="btn btn-sm btn-success"
                            data-confirm="Tandai laporan ini sebagai selesai karena barang sudah ditemukan atau urusan telah selesai?">
                        <i class="bi bi-check2-circle"></i> Tandai Barang Selesai
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Photo Column -->
        <div class="col-12 col-lg-5">
            <div class="card-surface overflow-hidden">
                <div class="img-shimmer-wrap skeleton-shimmer">
                    <?php
                    $fotoAda = !empty($barang->foto) && file_exists(UPLOAD_DIR . $barang->foto);
                    $fotoSrc = $fotoAda
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
                <!-- Badges -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if ($barang->jenis_laporan === 'hilang'): ?>
                        <span class="badge-rf badge-jenis-hilang">
                            <i class="bi bi-exclamation-circle"></i> Barang Hilang
                        </span>
                    <?php else: ?>
                        <span class="badge-rf badge-jenis-temuan">
                            <i class="bi bi-hand-index"></i> Barang Temuan
                        </span>
                    <?php endif; ?>

                    <span class="badge-rf <?= badge_status_class($barang->status) ?>">
                        <i class="bi bi-info-circle"></i> <?= escape($barang->status) ?>
                    </span>
                </div>

                <h1 class="h4 fw-bold mb-3"><?= escape($barang->nama_barang) ?></h1>

                <table class="table table-borderless detail-meta mb-3">
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
                <p class="mb-4 text-secondary" style="white-space: pre-line;"><?= escape($barang->deskripsi) ?></p>

                <!-- Alasan Penolakan (khusus pemilik / admin jika ditolak) -->
                <?php if ($barang->status === 'Ditolak' && !empty($barang->alasan_penolakan)): ?>
                    <div class="rejection-block mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Alasan Penolakan dari Admin:</strong> <?= escape($barang->alasan_penolakan) ?>
                    </div>
                <?php endif; ?>

                <!-- Contact Card (BR-08: Kontak hanya boleh dilihat pengguna yang telah login) -->
                <?php if (is_logged_in()): ?>
                    <div class="card-surface p-3" style="background-color: var(--rf-primary-soft);">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-telephone me-1 text-rf-primary"></i>Kontak Pelapor</h3>
                        <p class="mb-2 fw-medium font-mono"><?= escape($barang->kontak) ?></p>
                        <button type="button" class="btn btn-sm btn-rf-primary"
                                data-copy-text="<?= escape($barang->kontak) ?>">
                            <i class="bi bi-clipboard"></i> Salin Kontak
                        </button>
                    </div>
                <?php else: ?>
                    <div class="card-surface p-3 text-center" style="background-color: var(--rf-primary-soft);">
                        <i class="bi bi-shield-lock text-rf-primary fs-4 d-block mb-1"></i>
                        <h3 class="h6 fw-bold mb-1">Informasi Kontak Dilindungi</h3>
                        <p class="text-muted small mb-3">
                            Sesuai aturan privasi kampus (BR-08), silakan masuk dengan akun Anda untuk melihat kontak pelapor ini.
                        </p>
                        <a href="<?= base_url('pages/auth/login.php') ?>" class="btn btn-sm btn-rf-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk untuk Melihat Kontak
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
