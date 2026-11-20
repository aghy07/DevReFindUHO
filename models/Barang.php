<?php
/**
 * Model Barang — ReFindUHO
 * Mengelola data laporan barang hilang & temuan.
 */

class Barang
{
    private PDO $pdo;

    // Properti publik
    public int     $id_barang        = 0;
    public int     $id_user          = 0;
    public int     $id_kategori      = 0;
    public string  $jenis_laporan    = 'hilang';
    public string  $nama_barang      = '';
    public string  $deskripsi        = '';
    public string  $lokasi           = '';
    public string  $tanggal_kejadian = '';
    public string  $kontak           = '';
    public ?string $foto             = null;
    public string  $status           = 'Menunggu Verifikasi';
    public ?string $alasan_penolakan = null;
    public string  $created_at       = '';

    // Properti relasi (dari JOIN, opsional)
    public string  $nama_pelapor     = '';
    public string  $nama_kategori    = '';

    /**
     * @param PDO $pdo Koneksi database
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // -----------------------------------------------------------------
    // HELPER: Isi properti dari array asosiatif
    // -----------------------------------------------------------------
    private function isiDariArray(array $row): void
    {
        $this->id_barang        = (int) ($row['id_barang'] ?? 0);
        $this->id_user          = (int) ($row['id_user'] ?? 0);
        $this->id_kategori      = (int) ($row['id_kategori'] ?? 0);
        $this->jenis_laporan    = $row['jenis_laporan'] ?? 'hilang';
        $this->nama_barang      = $row['nama_barang'] ?? '';
        $this->deskripsi        = $row['deskripsi'] ?? '';
        $this->lokasi           = $row['lokasi'] ?? '';
        $this->tanggal_kejadian = $row['tanggal_kejadian'] ?? '';
        $this->kontak           = $row['kontak'] ?? '';
        $this->foto             = $row['foto'] ?? null;
        $this->status           = $row['status'] ?? 'Menunggu Verifikasi';
        $this->alasan_penolakan = $row['alasan_penolakan'] ?? null;
        $this->created_at       = $row['created_at'] ?? '';

        // Kolom relasi (dari JOIN)
        $this->nama_pelapor     = $row['nama_lengkap'] ?? '';
        $this->nama_kategori    = $row['nama_kategori'] ?? '';
    }

    // =================================================================
    // METHOD: simpan
    // Menyimpan laporan barang baru ke database.
    // Status awal otomatis 'Menunggu Verifikasi' (BR-04).
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function simpan(): bool
    {
        $this->nama_barang = trim($this->nama_barang);
        $this->deskripsi = trim($this->deskripsi);
        $this->lokasi = trim($this->lokasi);
        $this->kontak = trim($this->kontak);

        if (
            $this->id_user <= 0
            || $this->id_kategori <= 0
            || !in_array($this->jenis_laporan, ['hilang', 'temuan'], true)
            || $this->nama_barang === ''
            || $this->deskripsi === ''
            || $this->lokasi === ''
            || $this->tanggal_kejadian === ''
            || $this->kontak === ''
        ) {
            return false;
        }

        $sql = "INSERT INTO barang
                    (id_user, id_kategori, jenis_laporan, nama_barang, deskripsi,
                     lokasi, tanggal_kejadian, kontak, foto, status)
                VALUES
                    (:id_user, :id_kategori, :jenis_laporan, :nama_barang, :deskripsi,
                     :lokasi, :tanggal_kejadian, :kontak, :foto, 'Menunggu Verifikasi')";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':id_user'          => $this->id_user,
            ':id_kategori'      => $this->id_kategori,
            ':jenis_laporan'    => $this->jenis_laporan,
            ':nama_barang'      => $this->nama_barang,
            ':deskripsi'        => $this->deskripsi,
            ':lokasi'           => $this->lokasi,
            ':tanggal_kejadian' => $this->tanggal_kejadian,
            ':kontak'           => $this->kontak,
            ':foto'             => $this->foto,
        ]);

        if ($result) {
            $this->id_barang = (int) $this->pdo->lastInsertId();
        }

        return $result;
    }

    // =================================================================
    // METHOD: setujui
    // Mengubah status laporan menjadi 'Disetujui'.
    // Hanya berlaku jika status saat ini 'Menunggu Verifikasi'.
    // -----------------------------------------------------------------
    // CFG: 1 keputusan (status === 'Menunggu Verifikasi'), 2 jalur → CC = 2
    // =================================================================
    public function setujui(): bool
    {
        if ($this->status !== 'Menunggu Verifikasi') {
            return false;
        }

        $sql = "UPDATE barang
                SET status = 'Disetujui', alasan_penolakan = NULL
                WHERE id_barang = :id_barang
                  AND status = 'Menunggu Verifikasi'";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([':id_barang' => $this->id_barang]);

        if ($result && $stmt->rowCount() === 1) {
            $this->status = 'Disetujui';
            $this->alasan_penolakan = null;
            return true;
        }

        return false;
    }

    // =================================================================
    // METHOD: tolak
    // Mengubah status laporan menjadi 'Ditolak' dengan alasan wajib.
    // Hanya berlaku jika status saat ini 'Menunggu Verifikasi'.
    // Status 'Ditolak' bersifat akhir (final).
    // -----------------------------------------------------------------
    // CFG: 2 keputusan (status !== MV, alasan kosong), 3 jalur → CC = 3
    // =================================================================
    public function tolak(string $alasan): bool
    {
        if ($this->status !== 'Menunggu Verifikasi') {
            return false;
        }

        if (empty(trim($alasan))) {
            return false;
        }

        $sql = "UPDATE barang
                SET status = 'Ditolak', alasan_penolakan = :alasan
                WHERE id_barang = :id_barang
                  AND status = 'Menunggu Verifikasi'";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':alasan'    => trim($alasan),
            ':id_barang' => $this->id_barang,
        ]);

        if ($result && $stmt->rowCount() === 1) {
            $this->status           = 'Ditolak';
            $this->alasan_penolakan = trim($alasan);
            return true;
        }

        return false;
    }

    // =================================================================
    // METHOD: selesaikan
    // Mengubah status laporan menjadi 'Selesai'.
    // Hanya berlaku jika status saat ini 'Disetujui'.
    // Status 'Selesai' bersifat akhir (laporan hilang dari daftar
    // barang mahasiswa, tetapi tetap tampil di dashboard pemilik).
    // -----------------------------------------------------------------
    // CFG: 1 keputusan (status === 'Disetujui'), 2 jalur → CC = 2
    // =================================================================
    public function selesaikan(): bool
    {
        if ($this->status !== 'Disetujui') {
            return false;
        }

        $sql = "UPDATE barang
                SET status = 'Selesai'
                WHERE id_barang = :id_barang
                  AND status = 'Disetujui'";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([':id_barang' => $this->id_barang]);

        if ($result && $stmt->rowCount() === 1) {
            $this->status = 'Selesai';
            return true;
        }

        return false;
    }

    // =================================================================
    // METHOD: getStatus
    // Mengembalikan status laporan saat ini.
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function getStatus(): string
    {
        return $this->status;
    }

    // =================================================================
    // METHOD: tampilDiDaftar
    // Mengembalikan true jika laporan layak tampil di daftar barang
    // publik (hanya status 'Disetujui' yang ditampilkan — FR-05).
    // -----------------------------------------------------------------
    // CFG: 1 keputusan, 2 jalur → CC = 2
    // =================================================================
    public function tampilDiDaftar(): bool
    {
        return $this->status === 'Disetujui';
    }

    // =================================================================
    // METHOD: dimilikiOleh
    // Memeriksa apakah laporan ini dimiliki oleh pengguna tertentu.
    // -----------------------------------------------------------------
    // CFG: 1 keputusan, 2 jalur → CC = 2
    // =================================================================
    public function dimilikiOleh(int $id_user): bool
    {
        return $this->id_user === $id_user;
    }

    // =================================================================
    // METHOD: cariById
    // Mencari satu laporan barang berdasarkan id_barang, lengkap
    // dengan JOIN ke tabel users dan kategori.
    // -----------------------------------------------------------------
    // CFG: 1 keputusan, 2 jalur → CC = 2
    // =================================================================
    public function cariById(int $id_barang): ?self
    {
        $sql = "SELECT b.*, u.nama_lengkap, k.nama_kategori
                FROM barang b
                JOIN users u ON b.id_user = u.id_user
                JOIN kategori k ON b.id_kategori = k.id_kategori
                WHERE b.id_barang = :id_barang
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_barang' => $id_barang]);
        $row = $stmt->fetch();

        if ($row) {
            $obj = new self($this->pdo);
            $obj->isiDariArray($row);
            return $obj;
        }

        return null;
    }

    // =================================================================
    // METHOD: cariDisetujui
    // Mengambil laporan berstatus 'Disetujui' dengan pagination,
    // pencarian kata kunci, filter kategori, dan filter jenis laporan
    // yang bisa dikombinasikan (FR-05, FR-07).
    //
    // @param string $keyword   Kata kunci pencarian nama_barang
    // @param int|null $id_kategori Filter kategori
    // @param string|null $jenis    Filter jenis laporan (hilang/temuan)
    // @param int $halaman      Nomor halaman (mulai dari 1)
    // @param int $perHalaman   Jumlah per halaman (default 10)
    // @return array ['data' => [...], 'total' => int, 'halaman' => int,
    //                'totalHalaman' => int, 'perHalaman' => int]
    // -----------------------------------------------------------------
    // CFG: 3 keputusan (keyword, kategori, jenis), 4 jalur → CC = 4
    // =================================================================
    public function cariDisetujui(
        string $keyword = '',
        ?int $id_kategori = null,
        ?string $jenis = null,
        int $halaman = 1,
        int $perHalaman = 10
    ): array {
        $halaman = max(1, $halaman);
        $perHalaman = max(1, min($perHalaman, 100));
        $where  = "WHERE b.status = 'Disetujui'";
        $params = [];

        // Filter kata kunci
        if (!empty(trim($keyword))) {
            $where .= " AND b.nama_barang LIKE :keyword";
            $params[':keyword'] = '%' . trim($keyword) . '%';
        }

        // Filter kategori
        if ($id_kategori !== null && $id_kategori > 0) {
            $where .= " AND b.id_kategori = :id_kategori";
            $params[':id_kategori'] = $id_kategori;
        }

        // Filter jenis laporan
        if ($jenis !== null && in_array($jenis, ['hilang', 'temuan'], true)) {
            $where .= " AND b.jenis_laporan = :jenis";
            $params[':jenis'] = $jenis;
        }

        // Hitung total data
        $sqlCount = "SELECT COUNT(*) FROM barang b $where";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Hitung pagination
        $totalHalaman = ($total > 0) ? (int) ceil($total / $perHalaman) : 1;
        $halaman = max(1, min($halaman, $totalHalaman));
        $offset  = ($halaman - 1) * $perHalaman;

        // Ambil data dengan JOIN
        $sql = "SELECT b.*, u.nama_lengkap, k.nama_kategori
                FROM barang b
                JOIN users u ON b.id_user = u.id_user
                JOIN kategori k ON b.id_kategori = k.id_kategori
                $where
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'halaman'      => $halaman,
            'totalHalaman' => $totalHalaman,
            'perHalaman'   => $perHalaman,
        ];
    }

    // =================================================================
    // METHOD: cariByPemilik
    // Mengambil seluruh laporan milik seorang pengguna (BR-07).
    // Diurutkan dari terbaru.
    // -----------------------------------------------------------------
    // CFG: 1 jalur linear → CC = 1
    // =================================================================
    public function cariByPemilik(int $id_user): array
    {
        $sql = "SELECT b.*, u.nama_lengkap, k.nama_kategori
                FROM barang b
                JOIN users u ON b.id_user = u.id_user
                JOIN kategori k ON b.id_kategori = k.id_kategori
                WHERE b.id_user = :id_user
                ORDER BY b.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        return $stmt->fetchAll();
    }

    // =================================================================
    // METHOD: cariByStatus
    // Mengambil laporan berdasarkan status tertentu (untuk admin).
    // Jika status null atau 'Semua', ambil seluruh laporan.
    // -----------------------------------------------------------------
    // CFG: 1 keputusan (status kosong atau 'Semua'), 2 jalur → CC = 2
    // =================================================================
    public function cariByStatus(?string $status = null): array
    {
        $allowedStatus = ['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Selesai'];
        if ($status !== null && $status !== '' && $status !== 'Semua' && !in_array($status, $allowedStatus, true)) {
            return [];
        }

        $sql = "SELECT b.*, u.nama_lengkap, k.nama_kategori
                FROM barang b
                JOIN users u ON b.id_user = u.id_user
                JOIN kategori k ON b.id_kategori = k.id_kategori";

        $params = [];

        if (!empty($status) && $status !== 'Semua') {
            $sql .= " WHERE b.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
