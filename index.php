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

<main>
    <section class="hero-section">
        <div class="container container-rf">
            <div class="hero-layout">
                <div>
                    <span class="hero-kicker">Layanan civitas akademika UHO</span>
                    <h1 class="hero-title">Barang hilang atau ditemukan di kampus?</h1>
                    <p class="hero-copy">
                        ReFindUHO membantu mahasiswa melaporkan barang, menelusuri temuan,
                        dan menghubungkan kembali barang dengan pemiliknya melalui proses
                        yang terverifikasi.
                    </p>
                    <div class="hero-actions">
                        <?php if ($isLoggedIn && $role === 'mahasiswa'): ?>
                            <a href="<?= base_url('pages/mahasiswa/form_laporan.php') ?>" class="btn btn-rf-primary">
                                <i class="bi bi-plus-lg"></i> Buat laporan
                            </a>
                            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-rf-primary">
                                Telusuri barang <i class="bi bi-arrow-up-right"></i>
                            </a>
                        <?php elseif ($isLoggedIn && $role === 'admin'): ?>
                            <a href="<?= base_url('pages/admin/dashboard.php') ?>" class="btn btn-rf-primary">
                                <i class="bi bi-speedometer2"></i> Buka dashboard
                            </a>
                            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-rf-primary">
                                Telusuri barang <i class="bi bi-arrow-up-right"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('pages/auth/register.php') ?>" class="btn btn-rf-primary">
                                <i class="bi bi-plus-lg"></i> Laporkan barang
                            </a>
                            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-rf-primary">
                                Telusuri barang <i class="bi bi-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="hero-note">
                        <i class="bi bi-shield-check"></i>
                        Kontak pelapor hanya tersedia bagi pengguna yang masuk.
                    </p>
                </div>

                <aside class="hero-panel" aria-label="Informasi proses laporan">
                    <div class="hero-panel-label">
                        <span class="hero-panel-mark"><i class="bi bi-search-heart"></i></span>
                        Alur yang jelas
                    </div>
                    <h2>Setiap laporan ditangani dengan langkah yang terukur.</h2>
                    <ul class="hero-points">
                        <li>
                            <span class="hero-point-index">01</span>
                            <span>
                                <span class="hero-point-title">Ceritakan detailnya</span>
                                <span class="hero-point-copy">Tambahkan ciri barang, lokasi, dan waktu kejadian.</span>
                            </span>
                        </li>
                        <li>
                            <span class="hero-point-index">02</span>
                            <span>
                                <span class="hero-point-title">Tunggu verifikasi</span>
                                <span class="hero-point-copy">Admin meninjau laporan sebelum ditampilkan ke publik.</span>
                            </span>
                        </li>
                        <li>
                            <span class="hero-point-index">03</span>
                            <span>
                                <span class="hero-point-title">Terhubung dengan pelapor</span>
                                <span class="hero-point-copy">Koordinasikan pengambilan barang dengan aman.</span>
                            </span>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>

<!-- Latest Approved Items Preview (Jika ada) -->
<?php if (!empty($laporanTerbaru)): ?>
<section class="section-wrap bg-white border-bottom">
    <div class="container container-rf">
        <div class="section-heading">
            <div>
                <span class="section-kicker">Pembaruan kampus</span>
                <h2>Barang yang baru diverifikasi</h2>
                <p>Laporan terbaru yang siap ditelusuri oleh civitas akademika.</p>
            </div>
            <a href="<?= base_url('pages/mahasiswa/daftar_barang.php') ?>" class="btn btn-outline-rf-primary">
                Lihat semua <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach ($laporanTerbaru as $item): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <article class="card-interactive h-100 d-flex flex-column">
                        <div class="img-shimmer-wrap skeleton-shimmer" style="border-radius: 8px 8px 0 0;">
                            <?php
                            $fotoAda = !empty($item['foto']) && file_exists(UPLOAD_DIR . $item['foto']);
                            $fotoSrc = $fotoAda ? base_url('uploads/' . $item['foto']) : base_url('assets/images/no-image.svg');
                            ?>
                            <img src="<?= escape($fotoSrc) ?>" alt="<?= escape($item['nama_barang']) ?>"
                                onerror="this.src='<?= base_url('assets/images/no-image.svg') ?>'">
                        </div>
                        <div class="p-3 p-lg-4 d-flex flex-column flex-grow-1">
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
                            </article>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-wrap">
    <div class="container container-rf">
        <div class="section-heading">
            <div>
                <span class="section-kicker">Cara kerja</span>
                <h2>Dari laporan hingga barang kembali</h2>
                <p>Alur sederhana yang menjaga informasi tetap jelas bagi pelapor dan pencari.</p>
            </div>
        </div>
        <div class="row g-3">
            <?php
            $steps = [
                ['title' => 'Ajukan laporan', 'desc' => 'Isi informasi barang, waktu, lokasi, dan ciri yang membantu proses pencarian.'],
                ['title' => 'Admin meninjau', 'desc' => 'Laporan diperiksa sebelum dipublikasikan agar informasi tetap terjaga.'],
                ['title' => 'Koordinasikan pengambilan', 'desc' => 'Masuk untuk melihat kontak pelapor dan menyelesaikan serah terima.'],
            ];
            foreach ($steps as $i => $step):
            ?>
                <div class="col-12 col-md-4">
                    <article class="process-card">
                        <span class="process-card-index">0<?= $i + 1 ?></span>
                        <h3><?= $step['title'] ?></h3>
                        <p><?= $step['desc'] ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container container-rf">
        <div class="contact-panel">
            <div>
                <span class="section-kicker">Butuh bantuan?</span>
                <h2 class="mt-2">Layanan administrasi kampus</h2>
                <p>Hubungi administrator jika mengalami kendala saat menggunakan ReFindUHO.</p>
            </div>
            <a href="mailto:admin@uho.ac.id">
                <i class="bi bi-envelope me-2"></i>admin@uho.ac.id
            </a>
        </div>
    </div>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
