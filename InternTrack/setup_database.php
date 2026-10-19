<?php
// setup_database.php - Inisialisasi Database interntrack & Seeder
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // 1. Buat Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS interntrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE interntrack");
    
    // 2. Buat Tabel-tabel
    $tables = "
    -- Nonaktifkan pengecekan foreign key sementara
    SET FOREIGN_KEY_CHECKS = 0;
    
    DROP TABLE IF EXISTS monitoring;
    DROP TABLE IF EXISTS absensi;
    DROP TABLE IF EXISTS jurnal;
    DROP TABLE IF EXISTS penempatan;
    DROP TABLE IF EXISTS pembimbing;
    DROP TABLE IF EXISTS perusahaan;
    DROP TABLE IF EXISTS siswa;
    DROP TABLE IF EXISTS users;
    
    -- Tabel users
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role ENUM('admin', 'pembimbing', 'siswa') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    -- Tabel siswa
    CREATE TABLE siswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        nis VARCHAR(20) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        kelas VARCHAR(30) NOT NULL,
        jurusan VARCHAR(60) NOT NULL,
        no_hp VARCHAR(25) NOT NULL,
        alamat TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_siswa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    -- Tabel perusahaan
    CREATE TABLE perusahaan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_perusahaan VARCHAR(120) NOT NULL,
        bidang_usaha VARCHAR(100) NOT NULL,
        alamat TEXT NOT NULL,
        kontak VARCHAR(50) NOT NULL,
        email VARCHAR(100) NULL,
        penanggung_jawab VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    -- Tabel pembimbing
    CREATE TABLE pembimbing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        nip VARCHAR(30) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        no_hp VARCHAR(25) NOT NULL,
        email VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_pembimbing_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    -- Tabel penempatan
    CREATE TABLE penempatan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        perusahaan_id INT NOT NULL,
        pembimbing_id INT NOT NULL,
        tanggal_mulai DATE NOT NULL,
        tanggal_selesai DATE NOT NULL,
        status ENUM('Belum Mulai', 'Aktif', 'Selesai') NOT NULL DEFAULT 'Aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_penempatan_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
        CONSTRAINT fk_penempatan_perusahaan FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE CASCADE,
        CONSTRAINT fk_penempatan_pembimbing FOREIGN KEY (pembimbing_id) REFERENCES pembimbing(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    -- Tabel jurnal
    CREATE TABLE jurnal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        penempatan_id INT NULL,
        tanggal DATE NOT NULL,
        kegiatan TEXT NOT NULL,
        hasil TEXT NOT NULL,
        kendala TEXT NULL,
        catatan_pembimbing TEXT NULL,
        status_validasi ENUM('Menunggu', 'Disetujui', 'Revisi') DEFAULT 'Menunggu',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_jurnal_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
        CONSTRAINT fk_jurnal_penempatan FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    -- Tabel absensi
    CREATE TABLE absensi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        penempatan_id INT NULL,
        tanggal DATE NOT NULL,
        status ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') NOT NULL,
        keterangan VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_absensi_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
        CONSTRAINT fk_absensi_penempatan FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    -- Tabel monitoring
    CREATE TABLE monitoring (
        id INT AUTO_INCREMENT PRIMARY KEY,
        penempatan_id INT NOT NULL,
        pembimbing_id INT NOT NULL,
        siswa_id INT NOT NULL,
        tanggal_monitoring DATE NOT NULL,
        catatan TEXT NOT NULL,
        nilai INT NOT NULL,
        rekomendasi TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_monitoring_penempatan FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE CASCADE,
        CONSTRAINT fk_monitoring_pembimbing FOREIGN KEY (pembimbing_id) REFERENCES pembimbing(id) ON DELETE CASCADE,
        CONSTRAINT fk_monitoring_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    SET FOREIGN_KEY_CHECKS = 1;
    ";
    
    $pdo->exec($tables);
    echo "Tabel berhasil dibuat.\n";

    // 3. SEED DATA DUMMY
    // Hash default: 'password123' and 'admin123'
    $adminPasswordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $userPasswordHash = password_hash('password123', PASSWORD_BCRYPT);

    // a. Users Admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPasswordHash, 'Administrator InternTrack', 'admin']);
    
    // b. 10 Pembimbing
    $pembimbingList = [
        ['198501152010011001', 'Drs. Budi Santoso, M.Kom', '081234567801', 'budi.santoso@smk.sch.id', 'budi'],
        ['198703222012012002', 'Siti Rahmawati, S.Pd', '081234567802', 'siti.rahma@smk.sch.id', 'siti'],
        ['198207102008011003', 'Ahmad Fauzi, M.T', '081234567803', 'ahmad.fauzi@smk.sch.id', 'fauzi'],
        ['199011052015032004', 'Dewi Lestari, S.Kom', '081234567804', 'dewi.lestari@smk.sch.id', 'dewi'],
        ['198404182009021005', 'Hendra Gunawan, S.Kom', '081234567805', 'hendra.g@smk.sch.id', 'hendra'],
        ['198909122014022006', 'Rina Anggraini, M.Kom', '081234567806', 'rina.ang@smk.sch.id', 'rina'],
        ['198602252010031007', 'Eko Prasetyo, S.Pd', '081234567807', 'eko.prasetyo@smk.sch.id', 'eko'],
        ['199208302018012008', 'Nurul Hidayah, S.Kom', '081234567808', 'nurul.h@smk.sch.id', 'nurul'],
        ['198305142008031009', 'Agus Wibowo, M.Cs', '081234567809', 'agus.wibowo@smk.sch.id', 'agus'],
        ['198812202013022010', 'Fitri Handayani, S.Pd', '081234567810', 'fitri.h@smk.sch.id', 'fitri']
    ];

    $pembimbingIds = [];
    foreach ($pembimbingList as $idx => $p) {
        $username = 'pembimbing' . ($idx + 1);
        $stmt->execute([$username, $userPasswordHash, $p[1], 'pembimbing']);
        $userId = $pdo->lastInsertId();

        $stmtP = $pdo->prepare("INSERT INTO pembimbing (user_id, nip, nama, no_hp, email) VALUES (?, ?, ?, ?, ?)");
        $stmtP->execute([$userId, $p[0], $p[1], $p[2], $p[3]]);
        $pembimbingIds[] = $pdo->lastInsertId();
    }
    echo "10 Pembimbing & Users berhasil dibuat.\n";

    // c. 10 Perusahaan
    $perusahaanList = [
        ['PT Digital Solusindo Nusantara', 'Software Development & IT Solution', 'Jl. Sudirman Kav. 24, Jakarta Pusat', '021-5551234', 'info@digitalsolusindo.co.id', 'Bambang Triyono'],
        ['PT Telkom Akses Regional', 'Telekomunikasi & Jaringan', 'Jl. Pajajaran No. 88, Bogor', '0251-8334455', 'pkl@telkomakses.id', 'Rudi Hermawan'],
        ['CV Kreasi Visual Studio', 'UI/UX & Multimedia Production', 'Jl. Gajah Mada No. 12, Bandung', '022-7201928', 'halo@kreasivisual.com', 'Nadia Amanda'],
        ['PT Prima Cyber Teknologi', 'Network Infrastructure & Cyber Security', 'Jl. Pemuda No. 45, Semarang', '024-8451122', 'hrd@primacyber.com', 'Hadi Kusuma'],
        ['PT Inovasi Media Kreatif', 'Web & Mobile App Development', 'Jl. Malioboro No. 102, Yogyakarta', '0274-561234', 'career@inovasimedia.co.id', 'Dian Permana'],
        ['PT Surya Sistem Integrator', 'ERP & Database Management', 'Jl. Basuki Rahmat No. 70, Surabaya', '031-5349988', 'contact@suryasistem.com', 'Agung Wicaksono'],
        ['PT Nexus Artificial Intelligence', 'AI & Data Science', 'Jl. Rasuna Said Blok X, Jakarta Selatan', '021-5290123', 'admin@nexusai.co.id', 'Maya Susanti'],
        ['CV Mitra Informatika Jaya', 'Computer Hardware & Networking', 'Jl. Gatot Subroto No. 33, Malang', '0341-456789', 'mitrajaya@gmail.com', 'Surya Saputra'],
        ['PT Quantum Cloud Solution', 'Cloud Computing & DevOps', 'Jl. Diponegoro No. 15, Denpasar', '0361-234567', 'cloud@quantum.co.id', 'Wayan Artha'],
        ['PT Global Solusi Webindo', 'E-Commerce & Digital Marketing', 'Jl. Ahmad Yani No. 90, Surakarta', '0271-712345', 'info@solusiwebindo.com', 'Lestari Indah']
    ];

    $perusahaanIds = [];
    $stmtPer = $pdo->prepare("INSERT INTO perusahaan (nama_perusahaan, bidang_usaha, alamat, kontak, email, penanggung_jawab) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($perusahaanList as $pr) {
        $stmtPer->execute($pr);
        $perusahaanIds[] = $pdo->lastInsertId();
    }
    echo "10 Perusahaan berhasil dibuat.\n";

    // d. 25 Siswa
    $siswaData = [
        ['102401', 'Aditya Pratama', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '082111110001', 'Jl. Cempaka No. 1, Semarang'],
        ['102402', 'Bagus Wicaksono', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '082111110002', 'Jl. Melati No. 2, Semarang'],
        ['102403', 'Citra Kirana', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '082111110003', 'Jl. Mawar No. 3, Semarang'],
        ['102404', 'Dimas Anggara', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '082111110004', 'Jl. Anggrek No. 4, Semarang'],
        ['102405', 'Eka Putri Rahayu', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '082111110005', 'Jl. Kenanga No. 5, Semarang'],
        ['102406', 'Fajar Ramadhan', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '082111110006', 'Jl. Dahlia No. 6, Semarang'],
        ['102407', 'Gilang Dirga', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '082111110007', 'Jl. Flamboyan No. 7, Semarang'],
        ['102408', 'Hana Safitri', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '082111110008', 'Jl. Teratai No. 8, Semarang'],
        ['102409', 'Indra Setiawan', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '082111110009', 'Jl. Kamboja No. 9, Semarang'],
        ['102410', 'Joko Susilo', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '082111110010', 'Jl. Bougenville No. 10, Semarang'],
        ['102411', 'Kartika Sari', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '082111110011', 'Jl. Pandan No. 11, Semarang'],
        ['102412', 'Lukman Hakim', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '082111110012', 'Jl. Beringin No. 12, Semarang'],
        ['102413', 'Mega Utami', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '082111110013', 'Jl. Asoka No. 13, Semarang'],
        ['102414', 'Naufal Rizky', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '082111110014', 'Jl. Pinus No. 14, Semarang'],
        ['102415', 'Olivia Zalianty', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '082111110015', 'Jl. Cendana No. 15, Semarang'],
        ['102416', 'Panji Gumilang', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '082111110016', 'Jl. Merak No. 16, Semarang'],
        ['102417', 'Qori Sandioriva', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '082111110017', 'Jl. Rajawali No. 17, Semarang'],
        ['102418', 'Rian Hidayat', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '082111110018', 'Jl. Garuda No. 18, Semarang'],
        ['102419', 'Salma Salsabila', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '082111110019', 'Jl. Elang No. 19, Semarang'],
        ['102420', 'Taufik Hidayat', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '082111110020', 'Jl. Kasuari No. 20, Semarang'],
        ['102421', 'Umar Wirahadi', 'XII SIJA 1', 'Sistem Informatika Jaringan dan Aplikasi', '082111110021', 'Jl. Pahlawan No. 21, Semarang'],
        ['102422', 'Vina Panduwinata', 'XII SIJA 1', 'Sistem Informatika Jaringan dan Aplikasi', '082111110022', 'Jl. Veteran No. 22, Semarang'],
        ['102423', 'Wahyu Hidayat', 'XII SIJA 1', 'Sistem Informatika Jaringan dan Aplikasi', '082111110023', 'Jl. Diponegoro No. 23, Semarang'],
        ['102424', 'Yoga Pratama', 'XII SIJA 1', 'Sistem Informatika Jaringan dan Aplikasi', '082111110024', 'Jl. Gajah Mada No. 24, Semarang'],
        ['102425', 'Zahra Amalia', 'XII SIJA 1', 'Sistem Informatika Jaringan dan Aplikasi', '082111110025', 'Jl. Pemuda No. 25, Semarang']
    ];

    $siswaIds = [];
    $stmtS = $pdo->prepare("INSERT INTO siswa (user_id, nis, nama, kelas, jurusan, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($siswaData as $idx => $s) {
        $username = 'siswa' . ($idx + 1);
        $stmt->execute([$username, $userPasswordHash, $s[1], 'siswa']);
        $userId = $pdo->lastInsertId();

        $stmtS->execute([$userId, $s[0], $s[1], $s[2], $s[3], $s[4], $s[5]]);
        $siswaIds[] = $pdo->lastInsertId();
    }
    echo "25 Siswa & Akun User berhasil dibuat.\n";

    // e. 25 Penempatan PKL (semua 25 siswa ditempatkan pada 10 perusahaan dan 10 pembimbing)
    // Status: 21 Aktif, 3 Selesai, 1 Belum Mulai
    $penempatanIds = [];
    $stmtPen = $pdo->prepare("INSERT INTO penempatan (siswa_id, perusahaan_id, pembimbing_id, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($siswaIds as $idx => $sId) {
        $perId = $perusahaanIds[$idx % count($perusahaanIds)];
        $pemId = $pembimbingIds[$idx % count($pembimbingIds)];
        
        if ($idx < 20) {
            $status = 'Aktif';
            $tglMulai = '2026-07-01';
            $tglSelesai = '2026-12-31';
        } elseif ($idx < 23) {
            $status = 'Selesai';
            $tglMulai = '2026-01-05';
            $tglSelesai = '2026-06-30';
        } else {
            $status = 'Belum Mulai';
            $tglMulai = '2026-11-01';
            $tglSelesai = '2027-04-30';
        }

        $stmtPen->execute([$sId, $perId, $pemId, $tglMulai, $tglSelesai, $status]);
        $penempatanIds[] = $pdo->lastInsertId();
    }
    echo "25 Penempatan PKL berhasil dibuat.\n";

    // f. 35 Jurnal Kegiatan (Minimal 30)
    // Termasuk jurnal untuk hari ini 2026-10-19 agar dashboard "Jurnal Hari Ini" dinamis dan terisi!
    $kegiatanList = [
        ['Membuat Desain Database', 'ERD berhasil diselesaikan dan dinormalisasi', 'Kesulitan menentukan relasi kardinalitas many-to-many', 'Bagus, lanjutkan pembuatan tabel fisik'],
        ['Implementasi Modul Autentikasi', 'Halaman login dan middleware role siswa/admin selesai', 'Session timeout kadang belum ter-destroy sempurna', 'Pastikan session_destroy dipanggil saat logout'],
        ['Konfigurasi Web Server Apache & Nginx', 'Virtual host berhasil diuji dan berjalan optimal', 'Port conflict pada 8080', 'Ganti port pada berkas konfigurasi httpd.conf'],
        ['Slicing Desain Figma ke HTML5 & Tailwind CSS', 'Tampilan responsif untuk desktop dan mobile selesai', 'Breakpoint mobile navigation perlu penyesuaian', 'Gunakan kelas hidden md:flex untuk navigasi'],
        ['Pembuatan REST API Endpoint Siswa', 'Endpoint CRUD JSON berhasil diuji via Postman', 'Format error validation belum seragam', 'Bungkus response dalam struktur data, message, status'],
        ['Testing dan Debugging Query SQL', 'Query pencarian dan filter berjalan di bawah 50ms', 'Slow query pada tabel absensi tanpa index', 'Tambahkan index pada kolom tanggal dan siswa_id'],
        ['Pemasangan Kabel Jaringan LAN & Crimping RJ-45', '10 kabel UTP lolos pengujian LAN tester', 'Satu konektor pin 3 tidak tembus', 'Potong ulang dan pastikan urutan T568B presisi'],
        ['Instalasi dan Konfigurasi Mikrotik RouterOS', 'Bandwidth management queue tree dan hotspot aktif', 'IP pool sempat overlap', 'Cek subnet mask pada DHCP Server range'],
        ['Backup dan Replikasi Database MySQL', 'Automated script crontab mysqldump berjalan harian', 'Penyimpanan lokal cepat penuh', 'Implementasikan rotasi log dan retensi 14 hari'],
        ['Penyusunan Dokumentasi API & Manual Pengguna', 'Dokumentasi format OpenAPI/Swagger rampung', 'Belum ada panduan pemula berbahasa Indonesia', 'Tambahkan instruksi visual langkah demi langkah']
    ];

    $stmtJurnal = $pdo->prepare("INSERT INTO jurnal (siswa_id, penempatan_id, tanggal, kegiatan, hasil, kendala, catatan_pembimbing, status_validasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Buat 35 jurnal: 5 jurnal bertanggal hari ini 2026-10-19, sisanya tanggal-tanggal sebelumnya
    $tglJurnalList = [
        '2026-10-19', '2026-10-19', '2026-10-19', '2026-10-19', '2026-10-19', // 5 hari ini
        '2026-10-18', '2026-10-18', '2026-10-17', '2026-10-17', '2026-10-16',
        '2026-10-16', '2026-10-15', '2026-10-15', '2026-10-14', '2026-10-14',
        '2026-10-13', '2026-10-12', '2026-10-11', '2026-10-10', '2026-10-09',
        '2026-10-08', '2026-10-07', '2026-10-06', '2026-10-05', '2026-10-04',
        '2026-10-03', '2026-10-02', '2026-10-01', '2026-09-30', '2026-09-29',
        '2026-09-28', '2026-09-27', '2026-09-26', '2026-09-25', '2026-09-24'
    ];

    $validasiStatus = ['Disetujui', 'Disetujui', 'Disetujui', 'Menunggu', 'Revisi'];
    foreach ($tglJurnalList as $i => $tgl) {
        $sId = $siswaIds[$i % count($siswaIds)];
        $penId = $penempatanIds[$i % count($penempatanIds)];
        $template = $kegiatanList[$i % count($kegiatanList)];
        $vStat = $validasiStatus[$i % count($validasiStatus)];
        $catatan = ($vStat === 'Disetujui') ? $template[3] : (($vStat === 'Revisi') ? 'Harap melengkapi hasil dokumentasi foto kegiatan' : null);

        $stmtJurnal->execute([
            $sId, $penId, $tgl, $template[0], $template[1], $template[2], $catatan, $vStat
        ]);
    }
    echo "35 Jurnal Kegiatan berhasil dibuat.\n";

    // g. Minimal 60-80 Data Absensi (Syarat minimal 30)
    // Dibuat bervariasi agar menghasilkan:
    // - Siswa dengan >= 90% (Sangat Baik)
    // - Siswa dengan 75% - 89% (Baik)
    // - Siswa dengan 60% - 74% (Perlu Perhatian)
    // - Siswa dengan < 60% (Perlu Pembinaan)
    $stmtAbs = $pdo->prepare("INSERT INTO absensi (siswa_id, penempatan_id, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?)");
    
    // Rentang tanggal absensi 1-15 Oktober 2026 (10 hari kerja)
    $hariKerja = [
        '2026-10-01', '2026-10-02', '2026-10-05', '2026-10-06', '2026-10-07',
        '2026-10-08', '2026-10-09', '2026-10-12', '2026-10-13', '2026-10-14'
    ];

    $totalAbsensi = 0;
    // Beri absensi untuk 10 siswa aktif pertama (10 hari masing-masing = 100 catatan absensi)
    for ($sIdx = 0; $sIdx < 10; $sIdx++) {
        $sId = $siswaIds[$sIdx];
        $penId = $penempatanIds[$sIdx];

        foreach ($hariKerja as $dayIdx => $tgl) {
            // Simulasi skenario persentase kehadiran:
            // Siswa 0 & 1: 100% Hadir (Sangat Baik)
            // Siswa 2 & 3: 90% Hadir (1 Izin) -> Sangat Baik
            // Siswa 4 & 5: 80% Hadir (2 Sakit) -> Baik
            // Siswa 6 & 7: 70% Hadir (2 Izin, 1 Alpa) -> Perlu Perhatian
            // Siswa 8 & 9: 50% Hadir (3 Alpa, 2 Sakit) -> Perlu Pembinaan
            
            $stat = 'Hadir';
            $ket = 'Masuk kerja tepat waktu';
            
            if ($sIdx == 2 && $dayIdx == 4) { $stat = 'Izin'; $ket = 'Urusan keluarga'; }
            if ($sIdx == 3 && $dayIdx == 7) { $stat = 'Izin'; $ket = 'Kepentingan sekolah'; }
            
            if ($sIdx == 4 && ($dayIdx == 3 || $dayIdx == 8)) { $stat = 'Sakit'; $ket = 'Demam surat dokter'; }
            if ($sIdx == 5 && ($dayIdx == 2 || $dayIdx == 6)) { $stat = 'Sakit'; $ket = 'Flu'; }

            if ($sIdx == 6 && ($dayIdx == 1 || $dayIdx == 5)) { $stat = 'Izin'; $ket = 'Acara keluarga'; }
            if ($sIdx == 6 && $dayIdx == 9) { $stat = 'Alpa'; $ket = 'Tanpa keterangan'; }

            if ($sIdx == 7 && ($dayIdx == 0 || $dayIdx == 4)) { $stat = 'Izin'; $ket = 'Keluarga sakit'; }
            if ($sIdx == 7 && $dayIdx == 8) { $stat = 'Alpa'; $ket = 'Tidak hadir'; }

            if ($sIdx == 8 && in_array($dayIdx, [1, 3, 5])) { $stat = 'Alpa'; $ket = 'Tanpa kabar'; }
            if ($sIdx == 8 && in_array($dayIdx, [7, 9])) { $stat = 'Sakit'; $ket = 'Sakit kepala'; }

            if ($sIdx == 9 && in_array($dayIdx, [0, 2, 6])) { $stat = 'Alpa'; $ket = 'Tanpa keterangan'; }
            if ($sIdx == 9 && in_array($dayIdx, [4, 8])) { $stat = 'Sakit'; $ket = 'Istirahat dokter'; }

            $stmtAbs->execute([$sId, $penId, $tgl, $stat, $ket]);
            $totalAbsensi++;
        }
    }
    echo "$totalAbsensi Data Absensi berhasil dibuat (mencakup Sangat Baik, Baik, Perlu Perhatian, Perlu Pembinaan).\n";

    // h. 15 Data Monitoring (Minimal 10)
    $stmtMon = $pdo->prepare("INSERT INTO monitoring (penempatan_id, pembimbing_id, siswa_id, tanggal_monitoring, catatan, nilai, rekomendasi) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $monitoringList = [
        [0, 0, 0, '2026-08-15', 'Siswa mampu beradaptasi cepat dengan tim pengembang industri dan menguasai framework Laravel.', 92, 'Pertahankan inisiatif dan eksplorasi arsitektur microservices.'],
        [1, 1, 1, '2026-08-18', 'Kedisiplinan jam kerja sangat baik, aktif bertanya saat ada kendala teknis konfigurasi fiber optik.', 88, 'Tingkatkan kecepatan terminasi kabel ODP.'],
        [2, 2, 2, '2026-08-20', 'Kemampuan desain UI/UX kreatif dan estetis, prototype dapat dipahami oleh klien.', 95, 'Disarankan mengikuti sertifikasi desain standar industri.'],
        [3, 3, 3, '2026-08-25', 'Paham konsep keamanan jaringan dan firewall rule, troubleshooting dilakukan secara sistematis.', 87, 'Latih dokumentasi topologi jaringan dengan lebih rapi.'],
        [4, 4, 4, '2026-09-02', 'Menunjukkan kemajuan signifikan dalam pengkodean frontend React Native.', 85, 'Perlu mendalami state management seperti Redux atau Zustand.'],
        [5, 5, 5, '2026-09-05', 'Komunikasi dengan mentor industri sangat kooperatif, hasil query database MySQL terstruktur.', 89, 'Lanjutkan belajar index tuning dan stored procedure.'],
        [6, 6, 6, '2026-09-10', 'Kehadiran perlu ditingkatkan, ada beberapa hari izin. Namun pengerjaan tugas selesai.', 74, 'Wajib memperhatikan komitmen absensi dan jam masuk kantor.'],
        [7, 7, 7, '2026-09-15', 'Tingkat kehadiran menurun, sering terlambat. Perlu pembimbingan intensif dari guru dan sekolah.', 68, 'Jadwalkan konseling dengan guru BK dan orang tua siswa.'],
        [8, 8, 8, '2026-09-20', 'Siswa kurang aktif dan sering tidak masuk tanpa kabar (alpa). Performa di industri rendah.', 55, 'Surat peringatan pertama (SP1) dan panggilan orang tua ke sekolah.'],
        [9, 9, 9, '2026-09-22', 'Kehadiran sangat rendah (<60%). Mentor industri menyampaikan keluhan disiplin.', 50, 'Evaluasi penempatan dan pembinaan langsung oleh waka humas/hubin.'],
        [10, 0, 10, '2026-09-25', 'Penguasaan hardware server dan cabling sangat rapi, proaktif dalam tugas lapangan.', 90, 'Lanjutkan ke konfigurasi virtualization Proxmox.'],
        [11, 1, 11, '2026-09-28', 'Dapat mengonfigurasi router Cisco dengan baik, etos kerja profesional.', 86, 'Pelajari routing protocol BGP dan OSPF lebih mendalam.'],
        [12, 2, 12, '2026-10-02', 'Mampu membuat mock up web landing page e-commerce dengan interaksi interaktif.', 91, 'Eksplorasi animasi micro-interactions dengan Tailwind.'],
        [13, 3, 13, '2026-10-05', 'Menguasai instalasi Linux Debian Server untuk kebutuhan web hosting internal.', 84, 'Pelajari otomatisasi server dengan Ansible script.'],
        [14, 4, 14, '2026-10-10', 'Mampu berkolaborasi git branch dengan tim industri tanpa ada conflict merge.', 93, 'Sangat siap untuk langsung disalurkan kerja setelah lulus.']
    ];

    foreach ($monitoringList as $m) {
        $stmtMon->execute([
            $penempatanIds[$m[0]],
            $pembimbingIds[$m[1]],
            $siswaIds[$m[2]],
            $m[3], $m[4], $m[5], $m[6]
        ]);
    }
    echo "15 Data Monitoring berhasil dibuat.\n";

    echo "\n=== SEMUA DATA BERHASIL DIINISIALISASI ===\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
