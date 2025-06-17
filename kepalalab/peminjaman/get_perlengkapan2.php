<?php
session_start();
$conn = new mysqli("localhost", "root", "", "labinforpetra_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$laboratorium = $_GET['laboratorium'];

$sql_perlengkapan = "SELECT kode_seri, nama_perlengkapan, jenis_perlengkapan, set_komputer FROM perlengkapan WHERE laboratorium = ?";
$stmt = $conn->prepare($sql_perlengkapan);
$stmt->bind_param("s", $laboratorium);
$stmt->execute();
$result = $stmt->get_result();

$perlengkapan = [];
while ($row = $result->fetch_assoc()) {
    $perlengkapan[] = $row;
}

echo json_encode($perlengkapan);
$conn->close();
?>