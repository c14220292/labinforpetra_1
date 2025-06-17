<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

$_SESSION['success'] = "Peminjaman berhasil diselesaikan!";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input waktu_selesai
    $waktu_selesai = trim($_POST['waktu_selesai'] ?? '');
    if (empty($waktu_selesai)) {
        $_SESSION['error'] = "Waktu selesai harus diisi.";
        header("Location: peminjaman.php");
        exit();
    }

    // Validasi bahwa ada peminjaman_ids dikirim
    if (!isset($_POST['peminjaman_ids']) || !is_array($_POST['peminjaman_ids']) || count($_POST['peminjaman_ids']) === 0) {
        $_SESSION['error'] = "Tidak ada peminjaman yang dipilih!";
        header("Location: peminjaman.php");
        exit();
    }

    // Validasi format waktu_selesai (basic)
    if (DateTime::createFromFormat('Y-m-d\TH:i', $waktu_selesai) === false) {
        $_SESSION['error'] = "Format waktu selesai tidak valid.";
        header("Location: peminjaman.php");
        exit();
    }

    // Ubah format ke MySQL DATETIME
    $waktu_selesai_mysql = date('Y-m-d H:i:s', strtotime($waktu_selesai));

    // Koneksi database
    $conn = new mysqli("localhost", "root", "", "labinforpetra_db");
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Persiapkan prepared statement di awal
    $updatePeminjamanStmt = $conn->prepare("UPDATE peminjaman SET status = 'Selesai', waktu_pengembalian = ? WHERE id_peminjaman = ? AND status = 'Dalam Proses'");
    $selectPerlengkapanStmt = $conn->prepare("SELECT id_perlengkapan FROM peminjaman WHERE id_peminjaman = ?");
    $selectDetailPerlengkapanStmt = $conn->prepare("SELECT nama_perlengkapan, set_komputer, kode_seri, laboratorium FROM perlengkapan WHERE id_perlengkapan = ?");
    $updatePerlengkapanStmt = $conn->prepare("UPDATE perlengkapan SET kondisi = 'Tersedia', status = 'Bisa Dipakai' WHERE id_perlengkapan = ?");
    $laporanStmt = $conn->prepare("INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) VALUES (?, ?, ?, ?, ?)");

    if (!$updatePeminjamanStmt || !$selectPerlengkapanStmt || !$selectDetailPerlengkapanStmt || !$updatePerlengkapanStmt || !$laporanStmt) {
        $_SESSION['error'] = "Gagal mempersiapkan perintah SQL.";
        $conn->close();
        header("Location: peminjaman.php");
        exit();
    }

    $error_occurred = false;
    $user_email = $_SESSION['email'] ?? null;
    $waktu_laporan = date('Y-m-d H:i:s');

    foreach ($_POST['peminjaman_ids'] as $id) {
        $id = intval($id); // pastikan tipe integer

        // Update data di peminjaman
        $updatePeminjamanStmt->bind_param('si', $waktu_selesai_mysql, $id);
        if (!$updatePeminjamanStmt->execute()) {
            $error_occurred = true;
            continue;
        }

        // Ambil id_perlengkapan
        $selectPerlengkapanStmt->bind_param("i", $id);
        $selectPerlengkapanStmt->execute();
        $result = $selectPerlengkapanStmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $id_perlengkapan = $row['id_perlengkapan'];

            // Ambil detail perlengkapan
            $selectDetailPerlengkapanStmt->bind_param("i", $id_perlengkapan);
            $selectDetailPerlengkapanStmt->execute();
            $detailResult = $selectDetailPerlengkapanStmt->get_result();

            if ($detailRow = $detailResult->fetch_assoc()) {
                $nama_perlengkapan = $detailRow['nama_perlengkapan'];
                $set_komputer = $detailRow['set_komputer'];
                $kode_seri = $detailRow['kode_seri'];
                $laboratorium = $detailRow['laboratorium'];

                // Update data perlengkapan
                $updatePerlengkapanStmt->bind_param("i", $id_perlengkapan);
                if (!$updatePerlengkapanStmt->execute()) {
                    $error_occurred = true;
                }

                // Insert ke tabel laporan
                $jenis_laporan = "Pengembalian Peminjaman";
                $keterangan = "Perlengkapan $nama_perlengkapan - $set_komputer dengan kode seri $kode_seri pada laboratorium $laboratorium telah selesai dipinjam dan dikembalikan pada tanggal " . date('d-m-Y H:i', strtotime($waktu_selesai));
                $laporanStmt->bind_param("sssss", $jenis_laporan, $user_email, $keterangan, $laboratorium, $waktu_laporan);
                $laporanStmt->execute();
            }
        }
    }

    // Tutup semua statement
    $updatePeminjamanStmt->close();
    $selectPerlengkapanStmt->close();
    $selectDetailPerlengkapanStmt->close();
    $updatePerlengkapanStmt->close();
    $laporanStmt->close();
    $conn->close();

    if ($error_occurred) {
        $_SESSION['error'] = "Terjadi kesalahan saat memperbarui beberapa data peminjaman.";
    } else {
        $_SESSION['success'] = "Peminjaman berhasil diselesaikan.";
    }

    header("Location: peminjaman.php");
    exit();
} else {
    // Jika bukan POST, langsung redirect ke halaman peminjaman
    header("Location: peminjaman.php");
    exit();
}
?>