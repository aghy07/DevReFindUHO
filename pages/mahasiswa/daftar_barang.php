<?php
/**
 * pages/mahasiswa/daftar_barang.php — ReFindUHO
 * Halaman daftar & pencarian barang berstatus Disetujui (FR-05, FR-07).
 * Dapat diakses oleh publik (semua civitas akademika).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$laporanController = new LaporanController($pdo);

// Ambil parameter pencarian & filter
$keyword      = trim($_GET['keyword'] ?? '');
$id_kategori  = !empty($_GET['id_kategori']) ? (int) $_GET['id_kategori'] : null;
$jenis        = !empty($_GET['jenis']) ? $_GET['jenis'] : null;
$halaman      = max(1, (int) ($_GET['halaman'] ?? 1));

// Ambil data dari controller
$hasil         = $laporanController->daftarBarang($keyword, $id_kategori, $jenis, $halaman);
$daftarBarang  = $hasil['data'];
$total         = $hasil['total'];
$totalHalaman  = $hasil['totalHalaman'];
$halamanAktif  = $hasil['halaman'];

// Ambil kategori untuk dropdown filter
$daftarKategori = $laporanController->daftarKategori();

$pageTitle = 'Daftar Barang';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Daftar Barang Hilang & Temuan</h1>
            <p class="text-muted small mb-0">Menampilkan barang yang telah diverifikasi oleh tim admin kampus UHO</p>
        </div>
        <?php if (is_logged_in() && (current_user()['role'] ?? '') === 'mahasiswa'): ?>
            <a href="<?= base_url('pages/mahasiswa/form_laporan.php') ?>" class="btn btn-sm btn-rf-primary">
                <i class="bi bi-plus-circle"></i> Buat Laporan
            </a>
        <?php endif; ?>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-surface p-3 mb-4">
        <form id="form-pencarian-barang" method="GET" action="">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="keyword" class="form-label small fw-medium">Cari nama barang</label>
                    <input type="text" class="form-control" id="keyword" name="keyword"
                           value="<?= escape($keyword) ?>" placeholder="Kata kunci...">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="id_kategori" class="form-label small fw-medium">Kategori</label>
                    <select class="form-select" id="id_kategori" name="id_kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($daftarKategori as $kat): ?>
                            <option value="<?= (int) $kat['id_kategori'] ?>"
                                <?= ($id_kategori === (int) $kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= escape($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="jenis" class="form-label small fw-medium">Jenis Laporan</label>
                    <select class="form-select" id="jenis" name="jenis">
                        <option value="">Semua Jenis</option>
                        <option value="hilang" <?= ($jenis === 'hilang') ? 'selected' : '' ?>>Barang Hilang</option>
                        <option value="temuan" <?= ($jenis === 'temuan') ? 'selected' : '' ?>>Barang Temuan</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-rf-primary flex-fill">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Result info -->
    <?php if ($total > 0): ?>
        <p class="text-muted small mb-3">
            Menemukan <strong><?= $total ?></strong> laporan barang
            <?php if (!empty($keyword)): ?> dengan kata kunci "<em><?= escape($keyword) ?></em>"<?php endif; ?>
        </p>
    <?php endif; ?>

    <!-- Results Grid -->
    <div class="row g-3" id="grid-daftar-barang">
        <?php if (empty($daftarBarang)): ?>
            <div class="col-12">
                <div class="card-surface p-5 text-center">
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem; color: var(--rf-text-muted);"></i>
                    <p class="mb-2 fw-medium">Tidak ada laporan barang yang sesuai dengan kriteria pencarian</p>
                    <p class="text-muted small mb-3">Coba gunakan kata kunci lain atau reset filter untuk melihat semua barang.</p>
                    <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-sm btn-outline-rf-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($daftarBarang as $barang): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-interactive h-100 d-flex flex-column">
                        <!-- Photo -->
                        <div class="img-shimmer-wrap skeleton-shimmer" style="border-radius: 8px 8px 0 0;">
                            <?php
                            $fotoAda = !empty($barang['foto']) && file_exists(UPLOAD_DIR . $barang['foto']);
                            $fotoSrc = $fotoAda ? base_url('uploads/' . $barang['foto']) : base_url('assets/images/no-image.svg');
                            ?>
                            <img src="<?= escape($fotoSrc) ?>" alt="<?= escape($barang['nama_barang']) ?>"
                                 onerror="this.src='<?= base_url('assets/images/no-image.svg') ?>'">
                        </div>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <!-- Jenis Badge -->
                            <?php if ($barang['jenis_laporan'] === 'hilang'): ?>
                                <span class="badge-rf badge-jenis-hilang mb-2 align-self-start">
                                    <i class="bi bi-exclamation-circle"></i> Barang Hilang
                                </span>
                            <?php else: ?>
                                <span class="badge-rf badge-jenis-temuan mb-2 align-self-start">
                                    <i class="bi bi-hand-index"></i> Barang Temuan
                                </span>
                            <?php endif; ?>

                            <h2 class="h6 fw-bold mb-1 text-clamp-2"><?= escape($barang['nama_barang']) ?></h2>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-tag"></i> <?= escape($barang['nama_kategori']) ?>
                            </p>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-geo-alt"></i> <?= escape($barang['lokasi']) ?>
                            </p>
                            <p class="small text-muted mb-3 font-mono">
                                <i class="bi bi-calendar3"></i> <?= escape($barang['tanggal_kejadian']) ?>
                            </p>

                            <div class="mt-auto">
                                <a href="<?= base_url('pages/mahasiswa/detail_barang.php?id=' . (int) $barang['id_barang']) ?>"
                                   class="btn btn-sm btn-outline-rf-primary w-100">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalHalaman > 1): ?>
        <nav class="mt-4" aria-label="Navigasi halaman">
            <ul class="pagination justify-content-center mb-0">
                <?php
                $queryParams = $_GET;
                unset($queryParams['halaman']);
                $baseQuery = http_build_query($queryParams);
                $buildUrl = function (int $targetPage) use ($baseQuery): string {
                    return '?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'halaman=' . $targetPage;
                };
                ?>
                <li class="page-item <?= ($halamanAktif <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $buildUrl($halamanAktif - 1) ?>" aria-label="Sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                    <li class="page-item <?= ($i === $halamanAktif) ? 'active' : '' ?>">
                        <a class="page-link font-mono" href="<?= $buildUrl($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($halamanAktif >= $totalHalaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $buildUrl($halamanAktif + 1) ?>" aria-label="Berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
