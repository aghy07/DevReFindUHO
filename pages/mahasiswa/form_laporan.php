<?php
/**
 * pages/mahasiswa/form_laporan.php — ReFindUHO
 * Form pembuatan laporan barang hilang/temuan (FR-03, FR-04).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';

$authController    = new AuthController($pdo);
$authController->pastikanLogin();

// Pastikan hanya mahasiswa
if (current_user()['role'] !== 'mahasiswa') {
    set_flash('danger', 'Hanya mahasiswa yang dapat membuat laporan.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$laporanController = new LaporanController($pdo);
$daftarKategori    = $laporanController->daftarKategori();

$errors = [];
$old = [
    'jenis_laporan'    => 'hilang',
    'nama_barang'      => '',
    'id_kategori'      => '',
    'deskripsi'        => '',
    'lokasi'           => '',
    'tanggal_kejadian' => '',
    'kontak'           => '',
];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } else {
        $old = [
            'jenis_laporan'    => $_POST['jenis_laporan'] ?? 'hilang',
            'nama_barang'      => $_POST['nama_barang'] ?? '',
            'id_kategori'      => $_POST['id_kategori'] ?? '',
            'deskripsi'        => $_POST['deskripsi'] ?? '',
            'lokasi'           => $_POST['lokasi'] ?? '',
            'tanggal_kejadian' => $_POST['tanggal_kejadian'] ?? '',
            'kontak'           => $_POST['kontak'] ?? '',
        ];

        $jenis   = $_POST['jenis_laporan'] ?? 'hilang';
        $file    = $_FILES['foto'] ?? [];
        $id_user = current_user()['id_user'];

        if ($jenis === 'temuan') {
            $result = $laporanController->buatLaporanTemuan($_POST, $file, $id_user);
        } else {
            $result = $laporanController->buatLaporanHilang($_POST, $file, $id_user);
        }

        if ($result['success']) {
            set_flash('success', $result['message']);
            header('Location: ' . base_url('pages/mahasiswa/dashboard.php'));
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}

$pageTitle = 'Buat Laporan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-4">
    <div class="row justify-content-center">
        <div class="col-12" style="max-width: 680px;">
            <h1 class="h4 fw-bold mb-4">Buat Laporan Barang</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card-surface p-4">
                <form method="POST" action="" enctype="multipart/form-data" data-loading-submit>
                    <?= csrf_field() ?>

                    <!-- Jenis Laporan Radio Cards -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Jenis Laporan</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="radio-card-input" id="jenis-hilang"
                                       name="jenis_laporan" value="hilang"
                                       <?= ($old['jenis_laporan'] === 'hilang') ? 'checked' : '' ?>>
                                <label class="radio-card-label text-center" for="jenis-hilang">
                                    <i class="bi bi-exclamation-circle d-block mb-1" style="font-size: 1.25rem; color: var(--rf-jenis-hilang-text);"></i>
                                    <span class="fw-medium">Melaporkan Barang Hilang</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="radio-card-input" id="jenis-temuan"
                                       name="jenis_laporan" value="temuan"
                                       <?= ($old['jenis_laporan'] === 'temuan') ? 'checked' : '' ?>>
                                <label class="radio-card-label text-center" for="jenis-temuan">
                                    <i class="bi bi-hand-index d-block mb-1" style="font-size: 1.25rem; color: var(--rf-jenis-temuan-text);"></i>
                                    <span class="fw-medium">Melaporkan Barang Temuan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Nama Barang -->
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label fw-medium">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                               value="<?= escape($old['nama_barang']) ?>"
                               placeholder="Contoh: Laptop ASUS VivoBook" required maxlength="100">
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="id_kategori" class="form-label fw-medium">Kategori</label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih kategori barang</option>
                            <?php foreach ($daftarKategori as $kat): ?>
                                <option value="<?= (int) $kat['id_kategori'] ?>"
                                    <?= ((int) $old['id_kategori'] === (int) $kat['id_kategori']) ? 'selected' : '' ?>>
                                    <?= escape($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-medium">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"
                                  placeholder="Jelaskan ciri khusus barang..."
                                  required><?= escape($old['deskripsi']) ?></textarea>
                        <div class="form-text" id="hint-deskripsi">
                            Jelaskan ciri khusus, warna, merek, atau isi barang agar mudah dikenali.
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="mb-3">
                        <label for="lokasi" class="form-label fw-medium" id="label-lokasi">Lokasi Terakhir Terlihat</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi"
                               value="<?= escape($old['lokasi']) ?>"
                               placeholder="Contoh: Gedung Fakultas Teknik Lt. 2" required maxlength="150">
                    </div>

                    <!-- Tanggal Kejadian -->
                    <div class="mb-3">
                        <label for="tanggal_kejadian" class="form-label fw-medium" id="label-tanggal">Tanggal Hilang</label>
                        <input type="date" class="form-control font-mono" id="tanggal_kejadian" name="tanggal_kejadian"
                               value="<?= escape($old['tanggal_kejadian']) ?>"
                               max="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Kontak Pelapor -->
                    <div class="mb-3">
                        <label for="kontak" class="form-label fw-medium">Kontak Pelapor</label>
                        <input type="text" class="form-control" id="kontak" name="kontak"
                               value="<?= escape($old['kontak']) ?>"
                               placeholder="No. WhatsApp, LINE ID, atau kontak lainnya" required maxlength="100">
                    </div>

                    <!-- Foto (Opsional) -->
                    <div class="mb-4">
                        <label for="input-foto" class="form-label fw-medium">Foto Barang <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="file" class="form-control" id="input-foto" name="foto" accept="image/jpeg,image/png">
                        <div class="form-text">Format JPG atau PNG, maksimal 2 MB.</div>
                        <div id="foto-error-msg" class="text-danger small mt-1 d-none"></div>
                        <div id="preview-foto-wrap" class="mt-2 d-none" style="max-width: 240px;">
                            <img id="preview-foto-img" src="" alt="Pratinjau foto" class="img-fluid rounded" style="border: 1px solid var(--rf-border);">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-rf-primary w-100" data-loading-text="Mengirim laporan...">
                        <i class="bi bi-send"></i> Kirim Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
