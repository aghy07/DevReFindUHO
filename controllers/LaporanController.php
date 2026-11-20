<?php
/**
 * LaporanController — ReFindUHO
 * Mengelola pembuatan laporan, daftar barang, detail, pencarian,
 * verifikasi, dan perubahan status laporan.
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/Barang.php';
require_once __DIR__ . '/../models/Kategori.php';
require_once __DIR__ . '/../models/User.php';

class LaporanController
{
    private PDO      $pdo;
    private Barang   $barangModel;
    private Kategori $kategoriModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo           = $pdo;
        $this->barangModel   = new Barang($pdo);
        $this->kategoriModel = new Kategori($pdo);
    }

    // =================================================================
    // METHOD: validasiLaporan
    // Memvalidasi seluruh input form laporan (server-side) termasuk
    // validasi foto unggah (opsional, JPG/PNG, max 2 MB, rename unik).
    //
    // @param array $data  Data POST
    // @param array $file  Data $_FILES['foto']
    // @return array ['valid' => bool, 'errors' => [...], 'foto_path' => ?string]
    // -----------------------------------------------------------------
    // CFG: 10 keputusan → CC = 11
    // =================================================================
    public function validasiLaporan(array $data, array $file = []): array
    {
        $errors   = [];
        $fotoPath = null;

        // Validasi field wajib
        $requiredFields = [
            'nama_barang'      => 'Nama barang',
            'id_kategori'      => 'Kategori',
            'deskripsi'        => 'Deskripsi',
            'lokasi'           => 'Lokasi',
            'tanggal_kejadian' => 'Tanggal kejadian',
            'kontak'           => 'Kontak pelapor',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = "$label wajib diisi.";
            }
        }

        // Validasi jenis laporan
        $jenis = $data['jenis_laporan'] ?? '';
        if (!in_array($jenis, ['hilang', 'temuan'], true)) {
            $errors[] = 'Jenis laporan tidak valid.';
        }

        // Validasi kategori valid di database
        $id_kategori = (int) ($data['id_kategori'] ?? 0);
        if ($id_kategori > 0) {
            $kat = $this->kategoriModel->cariById($id_kategori);
            if ($kat === null) {
                $errors[] = 'Kategori yang dipilih tidak valid.';
            }
        }

        // Validasi tanggal tidak di masa depan
        $tanggal = $data['tanggal_kejadian'] ?? '';
        if (!empty($tanggal)) {
            $tglInput = strtotime($tanggal);
            if ($tglInput === false) {
                $errors[] = 'Format tanggal kejadian tidak valid.';
            } elseif ($tglInput > strtotime('today')) {
                $errors[] = 'Tanggal kejadian tidak boleh di masa depan.';
            }
        }

        // Validasi foto (opsional)
        if (!empty($file) && isset($file['tmp_name']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {

            // Cek error upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengunggah foto. Kode error: ' . $file['error'];
            } else {
                // Validasi format file (JPG/JPEG/PNG)
                $allowedMime = ['image/jpeg', 'image/png'];
                $allowedExt  = ['jpg', 'jpeg', 'png'];

                $finfo    = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($mimeType, $allowedMime, true) || !in_array($ext, $allowedExt, true)) {
                    $errors[] = 'Format foto harus JPG, JPEG, atau PNG.';
                }

                // Validasi ukuran file (max 2 MB)
                $maxSize = 2 * 1024 * 1024; // 2 MB
                if ($file['size'] > $maxSize) {
                    $errors[] = 'Ukuran foto maksimal 2 MB.';
                }

                // Jika lolos validasi, proses upload
                if (empty($errors)) {
                    // Buat nama file unik
                    $namaUnik = uniqid('rfuho_', true) . '.' . $ext;

                    // Pastikan folder uploads ada
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    $tujuan = UPLOAD_DIR . $namaUnik;

                    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                        $fotoPath = $namaUnik;
                    } else {
                        $errors[] = 'Gagal menyimpan foto. Periksa izin folder uploads/.';
                    }
                }
            }
        }

        return [
            'valid'     => empty($errors),
            'errors'    => $errors,
            'foto_path' => $fotoPath,
        ];
    }

    // =================================================================
    // METHOD: buatLaporanHilang
    // Membuat laporan barang hilang (FR-03). Jenis otomatis 'hilang'.
    // -----------------------------------------------------------------
    // CFG: 2 keputusan (validasi gagal, simpan gagal) → CC = 3
    // =================================================================
    public function buatLaporanHilang(array $data, array $file, int $id_user): array
    {
        $data['jenis_laporan'] = 'hilang';
        return $this->prosesLaporan($data, $file, $id_user);
    }

    // =================================================================
    // METHOD: buatLaporanTemuan
    // Membuat laporan barang temuan (FR-04). Jenis otomatis 'temuan'.
    // -----------------------------------------------------------------
    // CFG: 2 keputusan → CC = 3
    // =================================================================
    public function buatLaporanTemuan(array $data, array $file, int $id_user): array
    {
        $data['jenis_laporan'] = 'temuan';
        return $this->prosesLaporan($data, $file, $id_user);
    }

    // -----------------------------------------------------------------
    // INTERNAL: Proses laporan (dipakai oleh buatLaporanHilang/Temuan)
    // -----------------------------------------------------------------
    private function prosesLaporan(array $data, array $file, int $id_user): array
    {
        // Validasi input & foto
        $validasi = $this->validasiLaporan($data, $file);

        if (!$validasi['valid']) {
            return ['success' => false, 'errors' => $validasi['errors']];
        }

        // Siapkan model dan simpan
        $barang = new Barang($this->pdo);
        $barang->id_user          = $id_user;
        $barang->id_kategori      = (int) $data['id_kategori'];
        $barang->jenis_laporan    = $data['jenis_laporan'];
        $barang->nama_barang      = trim($data['nama_barang']);
        $barang->deskripsi        = trim($data['deskripsi']);
        $barang->lokasi           = trim($data['lokasi']);
        $barang->tanggal_kejadian = $data['tanggal_kejadian'];
        $barang->kontak           = trim($data['kontak']);
        $barang->foto             = $validasi['foto_path'];

        $berhasil = $barang->simpan();

        if ($berhasil) {
            return [
                'success' => true,
                'message' => 'Laporan barang berhasil dikirim dan sedang menunggu verifikasi admin.',
            ];
        }

        return ['success' => false, 'errors' => ['Gagal menyimpan laporan. Silakan coba lagi.']];
    }

    // =================================================================
    // METHOD: daftarBarang
    // Mengambil daftar barang berstatus 'Disetujui' dengan pagination,
    // pencarian, dan filter (FR-05, FR-07).
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear (delegasi ke model) → CC = 1
    // =================================================================
    public function daftarBarang(
        string $keyword = '',
        ?int $id_kategori = null,
        ?string $jenis = null,
        int $halaman = 1
    ): array {
        return $this->barangModel->cariDisetujui($keyword, $id_kategori, $jenis, $halaman, 10);
    }

    // =================================================================
    // METHOD: cariBarang
    // Alias pencarian barang — memanggil daftarBarang (FR-07).
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function cariBarang(
        string $keyword = '',
        ?int $id_kategori = null,
        ?string $jenis = null,
        int $halaman = 1
    ): array {
        return $this->daftarBarang($keyword, $id_kategori, $jenis, $halaman);
    }

    // =================================================================
    // METHOD: detailBarang
    // Mengambil detail satu barang untuk halaman detail mahasiswa.
    // Hanya mengembalikan barang berstatus 'Disetujui' (FR-06).
    // -----------------------------------------------------------------
    // CFG: 2 keputusan (tidak ditemukan, tidak disetujui) → CC = 3
    // =================================================================
    public function detailBarang(int $id_barang): ?Barang
    {
        $barang = $this->barangModel->cariById($id_barang);

        if ($barang === null) {
            return null;
        }

        // Hanya barang 'Disetujui' boleh diakses dari halaman publik
        if (!$barang->tampilDiDaftar()) {
            return null;
        }

        return $barang;
    }

    // =================================================================
    // METHOD: laporanSaya
    // Mengambil seluruh laporan milik mahasiswa yang sedang login
    // (FR-08, BR-07).
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function laporanSaya(int $id_user): array
    {
        return $this->barangModel->cariByPemilik($id_user);
    }

    // =================================================================
    // METHOD: laporanMasuk
    // Mengambil daftar laporan masuk untuk admin, dengan filter
    // status opsional (FR-09).
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function laporanMasuk(?string $status = null): array
    {
        return $this->barangModel->cariByStatus($status);
    }

    // =================================================================
    // METHOD: detailLaporanAdmin
    // Mengambil detail satu laporan untuk panel admin.
    // Tidak ada pembatasan status (admin boleh lihat semua).
    // -----------------------------------------------------------------
    // CFG: 1 keputusan → CC = 2
    // =================================================================
    public function detailLaporanAdmin(int $id_barang): ?Barang
    {
        return $this->barangModel->cariById($id_barang);
    }

    // =================================================================
    // METHOD: setujuiLaporan
    // Admin menyetujui laporan (FR-10). Status → 'Disetujui'.
    // -----------------------------------------------------------------
    // CFG: 2 keputusan → CC = 3
    // =================================================================
    public function setujuiLaporan(int $id_barang): array
    {
        $barang = $this->barangModel->cariById($id_barang);

        if ($barang === null) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan.'];
        }

        $berhasil = $barang->setujui();

        if ($berhasil) {
            return ['success' => true, 'message' => 'Laporan berhasil disetujui.'];
        }

        return ['success' => false, 'message' => 'Gagal menyetujui laporan. Pastikan status saat ini adalah "Menunggu Verifikasi".'];
    }

    // =================================================================
    // METHOD: tolakLaporan
    // Admin menolak laporan dengan alasan wajib (FR-10).
    // Status → 'Ditolak' (bersifat akhir / final).
    // -----------------------------------------------------------------
    // CFG: 3 keputusan → CC = 4
    // =================================================================
    public function tolakLaporan(int $id_barang, string $alasan): array
    {
        if (empty(trim($alasan))) {
            return ['success' => false, 'message' => 'Alasan penolakan wajib diisi.'];
        }

        $barang = $this->barangModel->cariById($id_barang);

        if ($barang === null) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan.'];
        }

        $berhasil = $barang->tolak($alasan);

        if ($berhasil) {
            return ['success' => true, 'message' => 'Laporan telah ditolak.'];
        }

        return ['success' => false, 'message' => 'Gagal menolak laporan. Pastikan status saat ini adalah "Menunggu Verifikasi".'];
    }

    // =================================================================
    // METHOD: selesaikanLaporan
    // Admin menandai laporan sebagai selesai (FR-11).
    // Status → 'Selesai' (bersifat akhir / final).
    // Laporan hilang dari daftar barang mahasiswa, tetapi tetap
    // tampil di dashboard pemilik.
    // -----------------------------------------------------------------
    // CFG: 2 keputusan → CC = 3
    // =================================================================
    public function selesaikanLaporan(int $id_barang): array
    {
        $barang = $this->barangModel->cariById($id_barang);

        if ($barang === null) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan.'];
        }

        $berhasil = $barang->selesaikan();

        if ($berhasil) {
            return ['success' => true, 'message' => 'Laporan telah ditandai selesai.'];
        }

        return ['success' => false, 'message' => 'Gagal menandai selesai. Pastikan status saat ini adalah "Disetujui".'];
    }

    // =================================================================
    // METHOD: daftarKategori
    // Mengambil seluruh kategori untuk dropdown form laporan.
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function daftarKategori(): array
    {
        return $this->kategoriModel->semua();
    }
}
