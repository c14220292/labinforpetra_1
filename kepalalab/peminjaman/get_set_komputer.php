<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "labinforpetra_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil laboratorium dari query parameter
$laboratorium = $_GET['laboratorium'];

// Ambil set komputer yang tersedia untuk perlengkapan di laboratorium yang dipilih
$sql_set_komputer = "SELECT DISTINCT set_komputer FROM perlengkapan WHERE laboratorium = ?";
$stmt = $conn->prepare($sql_set_komputer);
$stmt->bind_param("s", $laboratorium);
$stmt->execute();
$result = $stmt->get_result();

$sets = [];
while ($row = $result->fetch_assoc()) {
    $sets[] = $row['set_komputer'];
}

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode($sets);

$conn->close();
?>