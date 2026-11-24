<?php
/**
 * pages/admin/dashboard.php — ReFindUHO
 * Dashboard admin: ringkasan status & daftar laporan masuk (FR-09).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$authController    = new AuthController($pdo);
$authController->pastikanAdmin();

$laporanController = new LaporanController($pdo);

// Filter status dari query string (default: Menunggu Verifikasi)
$filterStatus = $_GET['status'] ?? 'Menunggu Verifikasi';
$validStatuses = ['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Selesai', 'Semua'];
if (!in_array($filterStatus, $validStatuses, true)) {
    $filterStatus = 'Menunggu Verifikasi';
}

// Ambil data untuk tabel
$laporanList = $laporanController->laporanMasuk($filterStatus === 'Semua' ? null : $filterStatus);

// Hitung ringkasan per status
$semuaLaporan = $laporanController->laporanMasuk(null);
$countByStatus = [
    'Menunggu Verifikasi' => 0,
    'Disetujui'           => 0,
    'Ditolak'             => 0,
    'Selesai'             => 0,
];
foreach ($semuaLaporan as $item) {
    if (isset($countByStatus[$item['status']])) {
        $countByStatus[$item['status']]++;
    }
}

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

function admin_badge_status_class(string $status): string {
    return match($status) {
        'Menunggu Verifikasi' => 'badge-status-wait',
        'Disetujui'           => 'badge-status-ok',
        'Ditolak'             => 'badge-status-no',
        'Selesai'             => 'badge-status-done',
        default               => 'badge-status-wait',
    };
}
?>

<main class="container container-rf py-4">
    <h1 class="h4 fw-bold mb-4">Dashboard Admin</h1>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <?php
        $summaryCards = [
            ['status' => 'Menunggu Verifikasi', 'icon' => 'bi-hourglass-split', 'label' => 'Menunggu Verifikasi'],
            ['status' => 'Disetujui',           'icon' => 'bi-check-circle',    'label' => 'Disetujui'],
            ['status' => 'Ditolak',             'icon' => 'bi-x-circle',        'label' => 'Ditolak'],
            ['status' => 'Selesai',             'icon' => 'bi-archive',         'label' => 'Selesai'],
        ];
        foreach ($summaryCards as $card):
            $isActive = ($filterStatus === $card['status']);
        ?>
            <div class="col-6 col-lg-3">
                <a href="?status=<?= urlencode($card['status']) ?>"
                   class="summary-card-link">
                    <div class="card-interactive p-3 text-center <?= $isActive ? 'border-primary' : '' ?>"
                         style="<?= $isActive ? 'border-color: var(--rf-primary); background-color: var(--rf-primary-soft);' : '' ?>">
                        <i class="bi <?= $card['icon'] ?> d-block mb-1" style="font-size: 1.5rem; color: var(--rf-primary);"></i>
                        <div class="h4 fw-bold mb-0"><?= $countByStatus[$card['status']] ?></div>
                        <div class="small text-muted"><?= $card['label'] ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter info -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0 fw-medium">
            Menampilkan: <span class="badge-rf <?= admin_badge_status_class($filterStatus) ?>"><?= escape($filterStatus) ?></span>
            <span class="text-muted small ms-1">(<?= count($laporanList) ?> laporan)</span>
        </p>
        <?php if ($filterStatus !== 'Semua'): ?>
            <a href="?status=Semua" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-list-ul"></i> Lihat Semua
            </a>
        <?php endif; ?>
    </div>

    <!-- Reports Table -->
    <?php if (empty($laporanList)): ?>
        <div class="card-surface p-5 text-center">
            <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem; color: var(--rf-text-muted);"></i>
            <p class="mb-0 fw-medium">Tidak ada laporan dengan status ini</p>
        </div>
    <?php else: ?>
        <div class="card-surface">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="small fw-semibold text-muted">Pelapor</th>
                            <th class="small fw-semibold text-muted">Jenis</th>
                            <th class="small fw-semibold text-muted">Nama Barang</th>
                            <th class="small fw-semibold text-muted">Tanggal Laporan</th>
                            <th class="small fw-semibold text-muted">Status</th>
                            <th class="small fw-semibold text-muted text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporanList as $item): ?>
                            <tr>
                                <td class="fw-medium"><?= escape($item['nama_lengkap']) ?></td>
                                <td>
                                    <?php if ($item['jenis_laporan'] === 'hilang'): ?>
                                        <span class="badge-rf badge-jenis-hilang">Hilang</span>
                                    <?php else: ?>
                                        <span class="badge-rf badge-jenis-temuan">Temuan</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= escape($item['nama_barang']) ?></td>
                                <td class="font-mono small"><?= escape($item['created_at']) ?></td>
                                <td>
                                    <span class="badge-rf <?= admin_badge_status_class($item['status']) ?>">
                                        <?= escape($item['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('pages/admin/detail_laporan.php?id=' . (int) $item['id_barang']) ?>"
                                       class="btn btn-sm btn-outline-rf-primary">
                                        <i class="bi bi-eye"></i> Periksa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
