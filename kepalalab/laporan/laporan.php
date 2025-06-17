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

// Ambil semua data laporan
$sql = "SELECT * FROM laporan ORDER BY waktu_laporan DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Laporan</title>
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

        main {
            padding: 20px 40px;
        }

        h2 {
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #01577d;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
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
            <form id="logoutForm" action="../../php/logout.php" method="post">
                <button type="button" onclick="confirmLogout()">Logout</button>
            </form>
        </div>
    </header>

    <main>
        <a href="../home.php">
            <button class="back-button">
                Kembali
            </button>
        </a>

        <h2>Daftar Laporan Aktivitas</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Jenis Laporan</th>
                <th>User</th>
                <th>Keterangan</th>
                <th>Laboratorium</th>
                <th>Waktu Laporan</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_laporan'] ?></td>
                        <td><?= htmlspecialchars($row['jenis_laporan']) ?></td>
                        <td><?= htmlspecialchars($row['user']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td><?= htmlspecialchars($row['laboratorium']) ?></td>
                        <td><?= $row['waktu_laporan'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Belum ada laporan yang tercatat.</td>
                </tr>
            <?php endif; ?>
        </table>
        <script>
            function togglePopup() {
                const popup = document.getElementById("userPopup");
                popup.style.display = popup.style.display === "block" ? "none" : "block";
            }

            window.onclick = function (event) {
                const overlay = document.getElementById("overlay");
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
    </main>
</body>

</html>