<?php
session_start();

// Pastikan hanya kepalalab yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

// Cek apakah parameter ID tersedia di URL
if (!isset($_GET['id'])) {
    header("Location: perlengkapan.php");
    exit();
}

$id = intval($_GET['id']); // Sanitize ID

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "labinforpetra_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data perlengkapan sebelum dihapus
$stmt_select = $conn->prepare("SELECT nama_perlengkapan, set_komputer, laboratorium FROM perlengkapan WHERE id_perlengkapan = ?");
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 0) {
    // Jika tidak ditemukan, kembali
    $stmt_select->close();
    $conn->close();
    header("Location: perlengkapan.php");
    exit();
}

$row = $result->fetch_assoc();
$nama_perlengkapan = $row['nama_perlengkapan'];
$set_komputer = $row['set_komputer'];
$kode_lab = $row['laboratorium'];

$stmt_select->close();

// Hapus data perlengkapan
$stmt_delete = $conn->prepare("DELETE FROM perlengkapan WHERE id_perlengkapan = ?");
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    // Tambahkan ke laporan
    $jenis_laporan = "Hapus Perlengkapan";
    $user = $_SESSION['email'];
    $keterangan = "Perlengkapan ($nama_perlengkapan - $set_komputer) telah dihapus";
    $waktu_laporan = date('Y-m-d H:i:s');

    $stmt_log = $conn->prepare("INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) VALUES (?, ?, ?, ?, ?)");
    $stmt_log->bind_param("sssss", $jenis_laporan, $user, $keterangan, $kode_lab, $waktu_laporan);
    $stmt_log->execute();
    $stmt_log->close();

    // Redirect kembali ke halaman perlengkapan
    header("Location: perlengkapan.php");
    exit();
} else {
    echo "Gagal menghapus data: " . $conn->error;
}

$stmt_delete->close();
$conn->close();
?>
