<?php
/**
 * pages/mahasiswa/daftar_barang.php — ReFindUHO
 * Halaman daftar & pencarian barang berstatus Disetujui (FR-05, FR-07).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$authController   = new AuthController($pdo);
$authController->pastikanLogin();

$laporanController = new LaporanController($pdo);

// Ambil parameter pencarian & filter
$keyword      = trim($_GET['keyword'] ?? '');
$id_kategori  = !empty($_GET['id_kategori']) ? (int) $_GET['id_kategori'] : null;
$jenis        = !empty($_GET['jenis']) ? $_GET['jenis'] : null;
$halaman      = max(1, (int) ($_GET['halaman'] ?? 1));

// Ambil data
$hasil     = $laporanController->daftarBarang($keyword, $id_kategori, $jenis, $halaman);
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
    <h1 class="h4 fw-bold mb-4">Daftar Barang Hilang & Temuan</h1>

    <!-- Search & Filter -->
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
                    <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Grid -->
    <div class="row g-3" id="grid-daftar-barang">
        <?php if (empty($daftarBarang)): ?>
            <div class="col-12">
                <div class="card-surface p-5 text-center">
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem; color: var(--rf-text-muted);"></i>
                    <p class="mb-2 fw-medium">Tidak ada laporan barang yang sesuai dengan kriteria pencarian</p>
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
                            $fotoSrc = !empty($barang['foto'])
                                ? base_url('uploads/' . $barang['foto'])
                                : base_url('assets/images/no-image.svg');
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
                // Build base query string without halaman
                $queryParams = $_GET;
                unset($queryParams['halaman']);
                $baseQuery = http_build_query($queryParams);
                ?>
                <li class="page-item <?= ($halamanAktif <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $baseQuery ?>&halaman=<?= $halamanAktif - 1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                    <li class="page-item <?= ($i === $halamanAktif) ? 'active' : '' ?>">
                        <a class="page-link font-mono" href="?<?= $baseQuery ?>&halaman=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($halamanAktif >= $totalHalaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $baseQuery ?>&halaman=<?= $halamanAktif + 1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
