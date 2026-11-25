<?php
/**
 * pages/auth/login.php — ReFindUHO
 * Halaman Login Modern, Clean, & Minimalis
 * Terinspirasi dari Swiss Utility & Desain Curved Visual Split Card
 */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Jika sudah login, arahkan ke dashboard masing-masing
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
        $identitas    = $_POST['identitas'] ?? '';
        $password     = $_POST['password'] ?? '';
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

if (!function_exists('escape')) {
    function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$flashMessages = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — ReFindUHO</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --rf-primary: #1E3A8A;
            --rf-primary-light: #2563EB;
            --rf-accent: #3B82F6;
            --rf-bg-page: #EEF2F6;
            --rf-surface: #FFFFFF;
            --rf-input-bg: #F4F6F9;
            --rf-text-dark: #0F172A;
            --rf-text-muted: #64748B;
            --rf-border-light: #E2E8F0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #F0F4F8 0%, #E2E8F0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--rf-text-dark);
            overflow-x: hidden;
            position: relative;
        }

        /* Background Decorative Floating Orbs (Subtle Parallax) */
        .ambient-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
            animation: floatOrb 14s ease-in-out infinite alternate;
        }
        .shape-1 {
            width: 360px;
            height: 360px;
            background: #93C5FD;
            top: -10%;
            left: -5%;
        }
        .shape-2 {
            width: 420px;
            height: 420px;
            background: #BFDBFE;
            bottom: -15%;
            right: -5%;
            animation-duration: 18s;
            animation-direction: alternate-reverse;
        }

        @keyframes floatOrb {
            0% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(25px) scale(1.06); }
            100% { transform: translateY(-20px) scale(0.96); }
        }

        /* Main Floating Modal Card */
        .login-card {
            background: var(--rf-surface);
            border-radius: 28px;
            box-shadow: 0 25px 65px -12px rgba(15, 23, 42, 0.16),
                        0 8px 24px -4px rgba(15, 23, 42, 0.08);
            width: 100%;
            max-width: 1060px;
            min-height: 620px;
            position: relative;
            z-index: 1;
            display: flex;
            overflow: hidden;
            animation: cardFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
        }

        .login-card:hover {
            box-shadow: 0 30px 75px -10px rgba(15, 23, 42, 0.20),
                        0 12px 30px -4px rgba(15, 23, 42, 0.10);
        }

        @keyframes cardFadeIn {
            0% {
                opacity: 0;
                transform: translateY(28px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Left Side: Form Section */
        .login-form-area {
            flex: 1.15;
            padding: 3.25rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            z-index: 2;
            background: #FFFFFF;
        }

        /* Top Brand Navigation */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: var(--rf-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: -0.02em;
        }

        .brand-circle {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
            transition: transform 0.3s ease;
        }

        .brand-logo:hover .brand-circle {
            transform: rotate(12deg) scale(1.08);
        }

        .nav-links a {
            color: var(--rf-text-muted);
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            margin-left: 1.25rem;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--rf-primary);
        }

        /* Form Titles */
        .form-eyebrow {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--rf-accent);
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 2.15rem;
            letter-spacing: -0.03em;
            color: var(--rf-text-dark);
            margin-bottom: 0.45rem;
        }

        .form-title span.dot {
            color: var(--rf-primary-light);
        }

        .form-subtext {
            color: var(--rf-text-muted);
            font-size: 0.92rem;
            margin-bottom: 1.75rem;
        }

        .form-subtext a {
            color: var(--rf-primary-light);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .form-subtext a:hover {
            color: var(--rf-primary);
            text-decoration: underline;
        }

        /* Modern Input Styling (Pill / Rounded Soft Surface) */
        .input-floating-group {
            position: relative;
            margin-bottom: 1.15rem;
        }

        .input-floating-group .form-control {
            background-color: var(--rf-input-bg);
            border: 1.5px solid transparent;
            border-radius: 14px;
            height: 52px;
            padding: 0 1.25rem;
            padding-right: 2.85rem;
            font-size: 0.94rem;
            font-weight: 500;
            color: var(--rf-text-dark);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .input-floating-group .form-control::placeholder {
            color: #94A3B8;
            font-weight: 400;
        }

        .input-floating-group .form-control:focus {
            background-color: #FFFFFF;
            border-color: var(--rf-primary-light);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            transform: translateY(-1px);
        }

        .input-floating-group .input-icon {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .input-floating-group .form-control:focus + .input-icon,
        .input-floating-group .form-control:focus ~ .btn-toggle-pass {
            color: var(--rf-primary-light);
        }

        .btn-toggle-pass {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94A3B8;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: color 0.2s ease, background 0.15s ease;
        }

        .btn-toggle-pass:hover {
            color: var(--rf-primary);
            background: rgba(148, 163, 184, 0.12);
        }

        /* Action Buttons */
        .btn-submit-login {
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
            color: #FFFFFF;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 0.98rem;
            height: 50px;
            border-radius: 25px;
            border: none;
            padding: 0 2rem;
            box-shadow: 0 8px 18px -4px rgba(37, 99, 235, 0.35);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit-login:hover {
            background: linear-gradient(135deg, #172554 0%, #1D4ED8 100%);
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(37, 99, 235, 0.45);
        }

        .btn-submit-login:active {
            transform: scale(0.975) translateY(1px);
        }

        .btn-secondary-action {
            background-color: var(--rf-input-bg);
            color: var(--rf-text-muted);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 0.92rem;
            height: 50px;
            border-radius: 25px;
            border: 1px solid transparent;
            padding: 0 1.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .btn-secondary-action:hover {
            background-color: #E2E8F0;
            color: var(--rf-text-dark);
            transform: translateY(-1px);
        }

        /* Right Side: Campus Visual Area with Smooth Wave Divider */
        .campus-visual-area {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: #0F172A;
            display: flex;
            align-items: flex-end;
            padding: 2.75rem 2.5rem;
        }

        .campus-image-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }

        .campus-aerial-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.04);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            filter: brightness(0.95) contrast(1.02);
            will-change: transform;
        }

        .login-card:hover .campus-aerial-img {
            transform: scale(1.08);
        }

        /* Gradient Overlay on Photo */
        .campus-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.12) 0%, rgba(15, 23, 42, 0.3) 50%, rgba(15, 23, 42, 0.85) 100%);
            z-index: 2;
        }

        /* Organic Smooth S-Curve Divider (like Anywhere App reference) */
        .organic-wave-divider {
            position: absolute;
            top: -2px;
            bottom: -2px;
            left: -1px;
            width: 120px;
            height: calc(100% + 4px);
            z-index: 3;
            pointer-events: none;
        }

        /* Floating Campus Badge over the image */
        .campus-badge-caption {
            position: relative;
            z-index: 4;
            color: #FFFFFF;
            animation: captionFloat 6s ease-in-out infinite alternate;
        }

        @keyframes captionFloat {
            0% { transform: translateY(0); }
            100% { transform: translateY(-6px); }
        }

        .campus-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .campus-badge-tag .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22C55E;
            box-shadow: 0 0 8px #22C55E;
        }

        .campus-title-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.45rem;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .campus-desc-text {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 0;
            line-height: 1.45;
            max-width: 320px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .campus-watermark {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            z-index: 4;
            opacity: 0.85;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.1em;
            pointer-events: none;
        }

        /* Alert styling */
        .custom-alert {
            border-radius: 12px;
            border: none;
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .login-card {
                flex-direction: column-reverse;
                min-height: auto;
                max-width: 520px;
            }

            .campus-visual-area {
                height: 230px;
                flex: none;
                padding: 1.75rem;
            }

            .organic-wave-divider {
                display: none;
            }

            .login-form-area {
                padding: 2.25rem 2rem;
            }

            .form-title {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 575px) {
            body {
                padding: 0.75rem;
            }

            .login-card {
                border-radius: 20px;
            }

            .login-form-area {
                padding: 1.75rem 1.25rem;
            }

            .campus-visual-area {
                height: 180px;
                padding: 1.25rem;
            }

            .btn-submit-login, .btn-secondary-action {
                width: 100%;
            }

            .actions-row {
                flex-direction: column-reverse;
                gap: 0.75rem !important;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Floating Orbs for Micro-interaction Background -->
    <div class="ambient-shape shape-1" id="orb-1"></div>
    <div class="ambient-shape shape-2" id="orb-2"></div>

    <!-- Main Card Container -->
    <div class="login-card" id="cardContainer">
        
        <!-- Left Side: Form Area -->
        <div class="login-form-area">
            
            <!-- Brand Top Header -->
            <div>
                <div class="brand-header">
                    <a href="<?= base_url('index.php') ?>" class="brand-logo" title="Kembali ke Beranda ReFindUHO">
                        <span class="brand-circle">
                            <i class="bi bi-search-heart"></i>
                        </span>
                        ReFindUHO
                    </a>
                    <div class="nav-links">
                        <a href="<?= base_url('index.php') ?>"><i class="bi bi-house me-1"></i>Beranda</a>
                        <a href="<?= base_url('pages/auth/register.php') ?>">Daftar</a>
                    </div>
                </div>

                <!-- Flash Message if Any -->
                <?php if (!empty($flashMessages)): ?>
                    <?php foreach ($flashMessages as $flash): ?>
                        <div class="alert alert-<?= escape($flash['type']) ?> custom-alert d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-6"></i>
                            <div><?= escape($flash['message']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger custom-alert" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong class="small">Perhatian:</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $error): ?>
                                <li><?= escape($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Form Intro -->
                <div class="mb-4">
                    <div class="form-eyebrow">
                        <i class="bi bi-shield-lock"></i> PORTAL MASUK
                    </div>
                    <h1 class="form-title">
                        Selamat Datang<span class="dot">.</span>
                    </h1>
                    <p class="form-subtext">
                        Belum punya akun? <a href="<?= base_url('pages/auth/register.php') ?>">Daftar akun mahasiswa</a>
                    </p>
                </div>

                <!-- Form Inputs -->
                <form method="POST" action="" id="loginForm">
                    <?= csrf_field() ?>

                    <!-- Identity Input (NIM / Email) -->
                    <div class="input-floating-group">
                        <input type="text" 
                               class="form-control font-mono" 
                               id="identitas" 
                               name="identitas" 
                               value="<?= escape($oldIdentitas) ?>" 
                               placeholder="NIM atau Alamat Email" 
                               required 
                               autofocus 
                               autocomplete="username">
                        <i class="bi bi-person input-icon"></i>
                    </div>

                    <!-- Password Input with Toggle -->
                    <div class="input-floating-group">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Kata Sandi Akun" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="btn-toggle-pass" id="btnTogglePass" aria-label="Lihat atau sembunyikan kata sandi">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <!-- Helper Tip -->
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-2 px-1">
                        <span class="text-muted small" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1"></i>Admin gunakan email institusi
                        </span>
                        <a href="<?= base_url('index.php') ?>" class="text-muted small text-decoration-none hover-primary" style="font-size: 0.8rem;">
                            Bantuan?
                        </a>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-3 actions-row">
                        <a href="<?= base_url('index.php') ?>" class="btn-secondary-action">
                            <i class="bi bi-arrow-left"></i> Beranda
                        </a>
                        <button type="submit" class="btn-submit-login flex-grow-1" id="btnSubmit">
                            <span>Masuk Sekarang</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bottom Footnote -->
            <div class="mt-4 pt-2 text-center text-md-start">
                <span class="text-muted" style="font-size: 0.76rem;">
                    ReFindUHO &copy; <?= date('Y') ?> &bull; Universitas Halu Oleo
                </span>
            </div>

        </div>

        <!-- Right Side: Campus Visual Area with S-Curve Wave -->
        <div class="campus-visual-area" id="visualArea">
            
            <!-- Campus Aerial Photo -->
            <div class="campus-image-wrapper">
                <img src="<?= base_url('assets/images/kampus-uho-aerial.jpg') ?>" 
                     alt="Foto Udara Kampus Universitas Halu Oleo" 
                     class="campus-aerial-img"
                     id="parallaxImg">
            </div>

            <!-- Overlay Gradient -->
            <div class="campus-overlay"></div>

            <!-- SVG S-Curve Wave Divider (matches the reference design) -->
            <svg class="organic-wave-divider" viewBox="0 0 120 700" preserveAspectRatio="none" fill="#FFFFFF">
                <path d="M0,0 
                         L30,0 
                         C85,120 5,230 45,350 
                         C85,470 15,580 50,700 
                         L0,700 Z"></path>
            </svg>

            <!-- Floating Campus Info Badge -->
            <div class="campus-badge-caption" id="captionBadge">
                <div class="campus-badge-tag">
                    <span class="pulse-dot"></span>
                    <span>Bumi Perkemahan &bull; Kampus Hijau</span>
                </div>
                <h2 class="campus-title-text">Universitas Halu Oleo</h2>
                <p class="campus-desc-text">
                    Sistem terpadu penelusuran dan pelaporan barang hilang & temuan untuk civitas akademika UHO.
                </p>
            </div>

            <!-- Subtle Logo Monogram Watermark -->
            <div class="campus-watermark">
                UHO
            </div>

        </div>

    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Scripts: Password Toggle, Subtle Parallax & Hover Polish -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Toggle Password Visibility
            const toggleBtn = document.getElementById('btnTogglePass');
            const passInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (toggleBtn && passInput) {
                toggleBtn.addEventListener('click', () => {
                    const isHidden = passInput.type === 'password';
                    passInput.type = isHidden ? 'text' : 'password';
                    toggleIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // 2. Submit Button Loading State
            const loginForm = document.getElementById('loginForm');
            const btnSubmit = document.getElementById('btnSubmit');

            if (loginForm && btnSubmit) {
                loginForm.addEventListener('submit', () => {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Memverifikasi...</span>
                    `;
                });
            }

            // 3. Smooth Light Parallax Effect on Mouse Move
            const card = document.getElementById('cardContainer');
            const parallaxImg = document.getElementById('parallaxImg');
            const orb1 = document.getElementById('orb-1');
            const orb2 = document.getElementById('orb-2');
            const captionBadge = document.getElementById('captionBadge');

            if (window.innerWidth > 992) {
                document.addEventListener('mousemove', (e) => {
                    const x = (e.clientX / window.innerWidth) - 0.5;
                    const y = (e.clientY / window.innerHeight) - 0.5;

                    // Subtle image translation
                    if (parallaxImg) {
                        parallaxImg.style.transform = `scale(1.06) translate(${x * -16}px, ${y * -14}px)`;
                    }

                    // Floating text gentle depth
                    if (captionBadge) {
                        captionBadge.style.transform = `translate(${x * 12}px, ${y * 10}px)`;
                    }

                    // Background ambient orbs
                    if (orb1) {
                        orb1.style.transform = `translate(${x * 40}px, ${y * 40}px)`;
                    }
                    if (orb2) {
                        orb2.style.transform = `translate(${x * -35}px, ${y * -35}px)`;
                    }
                });
            }
        });
    </script>
</body>
</html>
