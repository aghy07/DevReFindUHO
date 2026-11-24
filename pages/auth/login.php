<?php
/**
 * pages/auth/login.php — ReFindUHO
 * Halaman login pengguna (FR-02).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Jika sudah login, redirect ke dashboard yang sesuai
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
$oldIdentitas = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form login telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $identitas = $_POST['identitas'] ?? '';
        $password  = $_POST['password'] ?? '';
        $oldIdentitas = $identitas;

        $result = $authController->login($identitas, $password);

        if ($result['success']) {
            set_flash('success', $result['message']);
            header('Location: ' . $result['redirect']);
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}

$pageTitle = 'Masuk';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="container container-rf py-5">
    <div class="row justify-content-center">
        <div class="col-12" style="max-width: 440px;">
            <div class="card-surface p-4 p-sm-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-2" style="width: 56px; height: 56px;">
                        <i class="bi bi-box-arrow-in-right text-rf-primary fs-3"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Masuk ke ReFindUHO</h1>
                    <p class="text-muted small mb-0">Civitas Akademika Universitas Halu Oleo</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2 shadow-sm">
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
                        <label for="identitas" class="form-label fw-medium">NIM atau Alamat Email</label>
                        <input type="text" class="form-control" id="identitas" name="identitas"
                               value="<?= escape($oldIdentitas) ?>"
                               placeholder="Contoh: F1D119001 atau user@uho.ac.id" required autofocus>
                        <div class="form-text">Mahasiswa dapat menggunakan NIM atau Email. Admin menggunakan Email.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Masukkan kata sandi" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="password" aria-label="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-rf-primary w-100" data-loading-text="Memeriksa akun...">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0 small">
                    Belum memiliki akun mahasiswa?
                    <a href="<?= base_url('pages/auth/register.php') ?>" class="text-rf-primary fw-semibold text-decoration-none">Daftar Akun di sini</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
