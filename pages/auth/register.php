<?php
/**
 * pages/auth/register.php — ReFindUHO
 * Halaman registrasi mahasiswa baru (FR-01).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Jika sudah login, redirect
if (is_logged_in()) {
    $role = current_user()['role'] ?? '';
    $redirect = ($role === 'admin')
        ? base_url('pages/admin/dashboard.php')
        : base_url('pages/mahasiswa/dashboard.php');
    header('Location: ' . $redirect);
    exit;
}

$authController = new AuthController($pdo);
$errors = [];
$old = [
    'nama_lengkap' => '',
    'nim'           => '',
    'email'         => '',
];

// Proses form registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } else {
        $old = [
            'nama_lengkap' => $_POST['nama_lengkap'] ?? '',
            'nim'           => $_POST['nim'] ?? '',
            'email'         => $_POST['email'] ?? '',
        ];

        $result = $authController->registrasi($_POST);

        if ($result['success']) {
            set_flash('success', $result['message']);
            header('Location: ' . base_url('pages/auth/login.php'));
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}

$pageTitle = 'Daftar Akun';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-5">
    <div class="row justify-content-center">
        <div class="col-12" style="max-width: 460px;">
            <div class="card-surface p-4 p-sm-5">
                <h1 class="h4 fw-bold mb-1 text-center">Daftar Akun Mahasiswa</h1>
                <p class="text-center text-muted mb-4">Buat akun untuk melaporkan barang hilang atau temuan</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= escape($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" data-loading-submit>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label fw-medium">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                               value="<?= escape($old['nama_lengkap']) ?>"
                               placeholder="Masukkan nama lengkap" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="nim" class="form-label fw-medium">NIM</label>
                        <input type="text" class="form-control font-mono" id="nim" name="nim"
                               value="<?= escape($old['nim']) ?>"
                               placeholder="Masukkan Nomor Induk Mahasiswa" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= escape($old['email']) ?>"
                               placeholder="contoh@email.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Minimal 8 karakter" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="password" aria-label="Lihat sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="konfirmasi_password" class="form-label fw-medium">Konfirmasi Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"
                                   placeholder="Ulangi kata sandi" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="konfirmasi_password" aria-label="Lihat sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-rf-primary w-100" data-loading-text="Mendaftarkan...">
                        <i class="bi bi-person-plus"></i> Daftar Akun
                    </button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Sudah punya akun?
                    <a href="<?= base_url('pages/auth/login.php') ?>" class="text-rf-primary fw-medium">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
