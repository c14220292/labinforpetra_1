<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

$_SESSION['success_message'] = "Pemeliharaan berhasil diselesaikan!";

$conn = new mysqli("localhost", "root", "", "labinforpetra_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waktu_selesai = trim($_POST['waktu_selesai'] ?? '');
    if (empty($waktu_selesai)) {
        $_SESSION['error_message'] = "Waktu selesai harus diisi.";
        header("Location: pemeliharaan.php");
        exit();
    }

    if (!isset($_POST['pemeliharaan_ids']) || !is_array($_POST['pemeliharaan_ids']) || count($_POST['pemeliharaan_ids']) === 0) {
        $_SESSION['error_message'] = "Tidak ada pemeliharaan yang dipilih!";
        header("Location: pemeliharaan.php");
        exit();
    }

    $updatePemeliharaanStmt = $conn->prepare("UPDATE pemeliharaan SET waktu_pengembalian = ?, status = 'Selesai' WHERE id_pemeliharaan = ?");
    $selectPerlengkapanStmt = $conn->prepare("SELECT id_perlengkapan, laboratorium FROM pemeliharaan WHERE id_pemeliharaan = ?");
    $selectDetailPerlengkapanStmt = $conn->prepare("SELECT nama_perlengkapan, set_komputer, kode_seri FROM perlengkapan WHERE id_perlengkapan = ?");
    $updatePerlengkapanStmt = $conn->prepare("UPDATE perlengkapan SET kondisi = 'Tersedia', status = 'Bisa Dipakai' WHERE id_perlengkapan = ?");

    if (!$updatePemeliharaanStmt || !$selectPerlengkapanStmt || !$selectDetailPerlengkapanStmt || !$updatePerlengkapanStmt) {
        $_SESSION['error_message'] = "Gagal mempersiapkan perintah SQL.";
        header("Location: pemeliharaan.php");
        exit();
    }

    $user_email = $_SESSION['email'] ?? null; 

    if (empty($user_email)) {
        $_SESSION['error_message'] = "User tidak ditemukan!";
        header("Location: pemeliharaan.php");
        exit();
    }

    foreach ($_POST['pemeliharaan_ids'] as $id_pemeliharaan) {
        $id_pemeliharaan = intval($id_pemeliharaan);

        $updatePemeliharaanStmt->bind_param("si", $waktu_selesai, $id_pemeliharaan);
        if (!$updatePemeliharaanStmt->execute()) {
            $_SESSION['error_message'] = "Gagal memperbarui data pemeliharaan.";
            header("Location: pemeliharaan.php");
            exit();
        }

        $selectPerlengkapanStmt->bind_param("i", $id_pemeliharaan);
        $selectPerlengkapanStmt->execute();
        $result = $selectPerlengkapanStmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $id_perlengkapan = $row['id_perlengkapan'];
            $laboratorium = $row['laboratorium'];

            $selectDetailPerlengkapanStmt->bind_param("i", $id_perlengkapan);
            $selectDetailPerlengkapanStmt->execute();
            $detailResult = $selectDetailPerlengkapanStmt->get_result();
            if ($detailRow = $detailResult->fetch_assoc()) {
                $nama_perlengkapan = $detailRow['nama_perlengkapan'];
                $set_komputer = $detailRow['set_komputer'];
                $kode_seri = $detailRow['kode_seri'];

                $updatePerlengkapanStmt->bind_param("i", $id_perlengkapan);
                if (!$updatePerlengkapanStmt->execute()) {
                    $_SESSION['error_message'] = "Gagal memperbarui status perlengkapan.";
                    header("Location: pemeliharaan.php");
                    exit();
                }

                $jenis_laporan = "Pengembalian Pemeliharaan";
                $waktu_laporan = date('Y-m-d H:i:s');
                $keterangan = "Perlengkapan $nama_perlengkapan - $set_komputer dengan kode seri $kode_seri pada laboratorium $laboratorium telah selesai dipelihara dan dikembalikan pada tanggal " . date('d-m-Y H:i', strtotime($waktu_selesai)) . ".";

                $laporan_sql = "INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) 
                                VALUES (?, ?, ?, ?, ?)";
                $laporan_stmt = $conn->prepare($laporan_sql);
                $laporan_stmt->bind_param("sssss", $jenis_laporan, $user_email, $keterangan, $laboratorium, $waktu_laporan);
                $laporan_stmt->execute();
            }
        }
    }

    $updatePemeliharaanStmt->close();
    $selectPerlengkapanStmt->close();
    $selectDetailPerlengkapanStmt->close();
    $updatePerlengkapanStmt->close();

    $_SESSION['success_message'] = "Pemeliharaan berhasil diselesaikan!";
    header("Location: pemeliharaan.php");
    exit();
}

$conn->close();
?>