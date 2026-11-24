<?php
/**
 * pages/auth/login.php — ReFindUHO
 * Halaman login pengguna (FR-02).
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Jika sudah login, redirect ke dashboard
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
        $errors[] = 'Sesi tidak valid. Silakan muat ulang halaman.';
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
            <div class="card-surface p-4 p-sm-5">
                <h1 class="h4 fw-bold mb-1 text-center">Masuk ke ReFindUHO</h1>
                <p class="text-center text-muted mb-4">Gunakan NIM atau email untuk masuk</p>

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
                        <label for="identitas" class="form-label fw-medium">NIM atau Email</label>
                        <input type="text" class="form-control" id="identitas" name="identitas"
                               value="<?= escape($oldIdentitas) ?>"
                               placeholder="Masukkan NIM atau alamat email" required autofocus>
                        <div class="form-text">Admin masuk menggunakan alamat email.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Masukkan kata sandi" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="password" aria-label="Lihat sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-rf-primary w-100" data-loading-text="Memproses...">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Belum punya akun?
                    <a href="<?= base_url('pages/auth/register.php') ?>" class="text-rf-primary fw-medium">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
