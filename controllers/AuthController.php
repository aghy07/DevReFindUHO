<?php
/**
 * AuthController — ReFindUHO
 * Mengelola registrasi, login, logout, dan proteksi sesi.
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private PDO  $pdo;
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo       = $pdo;
        $this->userModel = new User($pdo);
    }

    // =================================================================
    // METHOD: registrasi
    // Mendaftarkan akun mahasiswa baru (FR-01).
    // Validasi: nama, NIM, email, password min 8, konfirmasi cocok,
    //           keunikan NIM & email.
    // Role otomatis 'mahasiswa'. Admin dibuat via SQL (BR-10).
    // -----------------------------------------------------------------
    // CFG: 7 keputusan → CC = 8
    // =================================================================
    public function registrasi(array $data): array
    {
        $errors = [];

        // Ambil & trim input
        $nama_lengkap     = trim($data['nama_lengkap'] ?? '');
        $nim              = trim($data['nim'] ?? '');
        $email            = strtolower(trim($data['email'] ?? ''));
        $password         = $data['password'] ?? '';
        $konfirmasi       = $data['konfirmasi_password'] ?? '';

        // Validasi: nama tidak boleh kosong
        if (empty($nama_lengkap)) {
            $errors[] = 'Nama lengkap wajib diisi.';
        }

        // Validasi: NIM wajib untuk mahasiswa
        if (empty($nim)) {
            $errors[] = 'NIM wajib diisi.';
        }

        // Validasi: email tidak boleh kosong dan harus valid
        if (empty($email)) {
            $errors[] = 'Email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }

        // Validasi: password minimal 8 karakter
        if (strlen($password) < 8) {
            $errors[] = 'Kata sandi minimal 8 karakter.';
        }

        // Validasi: konfirmasi password harus cocok
        if ($password !== $konfirmasi) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        // Jika ada error validasi format, kembalikan langsung
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Validasi: keunikan NIM dan Email
        $duplikat = $this->userModel->nimAtauEmailTerdaftar($nim, $email);

        if (in_array('nim', $duplikat, true)) {
            $errors[] = 'NIM sudah terdaftar dalam sistem.';
        }
        if (in_array('email', $duplikat, true)) {
            $errors[] = 'Email sudah terdaftar dalam sistem.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Simpan user baru
        $this->userModel->nama_lengkap = $nama_lengkap;
        $this->userModel->nim          = $nim;
        $this->userModel->email        = $email;
        $this->userModel->password     = $password; // akan di-hash di model
        $this->userModel->role         = 'mahasiswa';

        try {
            $berhasil = $this->userModel->simpan();
        } catch (PDOException $exception) {
            error_log('ReFindUHO failed to register user: ' . $exception->getMessage());
            return ['success' => false, 'errors' => ['Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']];
        }

        if ($berhasil) {
            return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.'];
        }

        return ['success' => false, 'errors' => ['Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']];
    }

    // =================================================================
    // METHOD: login
    // Login pengguna menggunakan NIM/email dan kata sandi (FR-02).
    // Mahasiswa bisa login via NIM atau email; admin via email.
    // Setelah berhasil, data user disimpan di $_SESSION['user'].
    // -----------------------------------------------------------------
    // CFG: 3 keputusan → CC = 4
    // =================================================================
    public function login(string $identitas, string $password): array
    {
        // Validasi input tidak boleh kosong
        if (empty(trim($identitas)) || empty($password)) {
            return ['success' => false, 'errors' => ['NIM/Email dan kata sandi wajib diisi.']];
        }

        // Cari user berdasarkan NIM atau email
        try {
            $user = $this->userModel->cariByIdentitas(trim($identitas));
        } catch (PDOException $exception) {
            error_log('ReFindUHO failed to login user: ' . $exception->getMessage());
            return ['success' => false, 'errors' => ['Layanan sementara tidak tersedia. Silakan coba lagi.']];
        }

        if ($user === null) {
            return ['success' => false, 'errors' => ['Akun dengan NIM/Email tersebut tidak ditemukan.']];
        }

        // Verifikasi password
        if (!$user->cekPassword($password)) {
            return ['success' => false, 'errors' => ['Kata sandi yang Anda masukkan salah.']];
        }

        // Simpan data ke session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id_user'      => $user->id_user,
            'nama_lengkap' => $user->nama_lengkap,
            'nim'          => $user->nim,
            'email'        => $user->email,
            'role'         => $user->role,
        ];

        // Tentukan redirect berdasarkan role
        $redirect = ($user->isAdmin())
            ? base_url('pages/admin/dashboard.php')
            : base_url('pages/mahasiswa/dashboard.php');

        return ['success' => true, 'redirect' => $redirect, 'message' => 'Login berhasil!'];
    }

    // =================================================================
    // METHOD: logout
    // Menghapus seluruh data session dan mengarahkan ke halaman login.
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . base_url('pages/auth/login.php'));
        exit;
    }

    // =================================================================
    // METHOD: pastikanLogin
    // Memeriksa apakah pengguna sudah login. Jika belum, redirect
    // ke halaman login.
    // -----------------------------------------------------------------
    // CFG: 1 keputusan, 2 jalur → CC = 2
    // =================================================================
    public function pastikanLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            set_flash('warning', 'Anda harus login terlebih dahulu untuk mengakses halaman ini.');
            header('Location: ' . base_url('pages/auth/login.php'));
            exit;
        }
    }

    // =================================================================
    // METHOD: pastikanAdmin
    // Memeriksa apakah pengguna yang login adalah admin. Jika bukan,
    // redirect ke halaman beranda.
    // -----------------------------------------------------------------
    // CFG: 2 keputusan (belum login, bukan admin), 3 jalur → CC = 3
    // =================================================================
    public function pastikanAdmin(): void
    {
        $this->pastikanLogin();

        if ($_SESSION['user']['role'] !== 'admin') {
            set_flash('danger', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            header('Location: ' . base_url('index.php'));
            exit;
        }
    }
}
