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

// Ambil ID pengguna dari session
$id_pengguna = $_SESSION['id_pengguna'];

// Query ambil lab milik pengguna yang login
$sql = "SELECT l.kode_lab, l.nama_lab 
        FROM pengguna_lab pl 
        JOIN laboratorium l ON pl.kode_lab = l.kode_lab 
        WHERE pl.id_pengguna = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result = $stmt->get_result();

// Ambil semua perlengkapan dari lab yang dapat diakses pengguna
$sql_all = "SELECT * FROM perlengkapan p 
            JOIN pengguna_lab pl ON p.laboratorium = pl.kode_lab 
            WHERE pl.id_pengguna = ?";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("i", $id_pengguna);
$stmt_all->execute();
$result_all = $stmt_all->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laboratorium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        /*===============*/
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            position: relative;
            border-bottom: 4px solid #f7941d;
        }

        .logos {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .logos img {
            height: 40px;
        }

        .user-box {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 8px 12px;
            background-color: #f5f5f5;
            cursor: pointer;
        }

        .user-box span {
            font-size: 14px;
            margin-right: 8px;
        }

        .user-popup {
            display: none;
            position: absolute;
            top: 70px;
            right: 40px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 999;
            width: 300px;
        }

        .user-popup p {
            margin: 8px 0;
            font-size: 14px;
        }

        .user-popup button {
            margin-top: 10px;
            background-color: #f26522;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        /*===============*/

        .main {
            padding: 20px 40px;
        }

        .top-buttons {
            display: flex;
            gap: 24px;
            margin-bottom: 30px;
        }

        .top-buttons .btn {
            background: linear-gradient(135deg, #24345C, #44547C);
            ;
            width: 240px;
            height: 140px;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            font-size: 24px;
        }

        .top-buttons:hover {
            transform: translateY(-5px);
        }

        .btn-all {
            background: linear-gradient(135deg, #f7941d, #f26522);
        }

        .btn-tambah {
            background: #999;
        }

        .btn-hapus {
            background: #111;
        }

        .btn-edit {
            background: linear-gradient(135deg, #24345C, #44547C);
        }

        h2 {
            border-bottom: 4px solid orange;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #1d3c74;
        }

        .lab-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .lab-box {
            width: 240px;
            height: 140px;
            background: linear-gradient(135deg, #f7941d, #f26522);
            color: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .lab-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .lab-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .back-button {
            background-color: lightgray;
            color: black;
            border: none;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
        <div class="logos">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/4/4d/UK_PETRA_LOGO.svg/1200px-UK_PETRA_LOGO.svg.png"
                alt="Petra Logo" />
            <img src="https://petra.ac.id/img/logo-text.2e8a4502.png" alt="PCU Logo" />
        </div>
        <div class="user-box" onclick="togglePopup()">
            <span><?php echo $_SESSION['nama']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-popup" id="userPopup">
            <p><strong>Nama:</strong> <?php echo $_SESSION['nama']; ?></p>
            <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
            <form id="logoutForm" action="../php/logout.php" method="post" style="display: inline;">
                <button type="button" onclick="confirmLogout()">Logout</button>
            </form>
        </div>
    </header>


    <main class="main">
        <a href="home.php">
            <button class="back-button">
                Kembali
            </button>
        </a>


        <h2>PILIH LOKASI DATA</h2>

        <div class="top-buttons">
            <button onclick="window.location.href='perlengkapan.php?kode_lab=all'" class="btn btn-all">
                ALL<br>Master Data
            </button>
        </div>

        <div class="lab-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="lab-box"
                        onclick="window.location.href='perlengkapan.php?kode_lab=<?php echo urlencode($row['kode_lab']); ?>'">
                        <div class="lab-title"><?php echo htmlspecialchars($row['kode_lab']); ?></div>
                        <div><?php echo htmlspecialchars($row['nama_lab']); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada data laboratorium.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function togglePopup() {
            const popup = document.getElementById("userPopup");
            popup.style.display = popup.style.display === "block" ? "none" : "block";
        }

        window.onclick = function (event) {
            const popup = document.getElementById("userPopup");
            if (!event.target.closest(".user-box") && !event.target.closest("#userPopup")) {
                popup.style.display = "none";
            }
        };

        function confirmLogout() {
            const confirmation = confirm("Apakah Anda yakin ingin logout?");
            if (confirmation) {
                document.getElementById("logoutForm").submit();
            }
        }
    </script>
</body>

</html>

<?php
$conn->close(); // Tutup koneksi database
?>