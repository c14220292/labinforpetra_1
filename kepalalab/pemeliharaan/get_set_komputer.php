<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "labinforpetra_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$laboratorium = $_GET['laboratorium'];

$sql_set_komputer = "SELECT DISTINCT set_komputer FROM perlengkapan WHERE laboratorium = ?";
$stmt = $conn->prepare($sql_set_komputer);
$stmt->bind_param("s", $laboratorium);
$stmt->execute();
$result = $stmt->get_result();

$sets = [];
while ($row = $result->fetch_assoc()) {
    $sets[] = $row['set_komputer'];
}

header('Content-Type: application/json');
echo json_encode($sets);

$conn->close();
?>