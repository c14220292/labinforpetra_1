<?php
session_start();
// Pastikan pengguna sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}
// HomePage
// Branch Edbert 3
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda Admin</title>
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

        .menu-section {
            padding: 20px 40px;
        }

        .menu-title {
            font-size: 22px;
            font-weight: bold;
            color: #1d3c74;
            border-bottom: 4px solid #f5a623;
            width: fit-content;
            margin-bottom: 20px;
        }

        .menu-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .menu-item {
            width: 220px;
            height: 180px;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            cursor: pointer;
            padding: 20px;
            transition: transform 0.2s ease;
            text-decoration: none;
        }

        .menu-item:hover {
            transform: scale(1.05);
        }

        .menu-data,
        .menu-peminjaman,
        .menu-tambah-perlengkapan {
            background: linear-gradient(135deg, #f7941d, #f26522);
        }

        .menu-pemeliharaan,
        .menu-laporan {
            background: linear-gradient(135deg, #24345C, #44547C);
        }

        .menu-item i {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .menu-item span {
            font-weight: bold;
            font-size: 18px;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 20px;
            width: 300px;
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: gray;
        }

        .popup-button {
            margin: 10px 0;
            padding: 10px;
            background-color: #f26522;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .popup-button:hover {
            background-color: #d95420;
        }

        .popup-button-mt {
            margin: 10px 0;
            padding: 10px;
            background-color: #44547C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .popup-button-mt:hover {
            background-color: #24345C;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
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

    <div class="menu-section">
        <div class="menu-title">MENU APLIKASI</div>
        <div class="menu-container">
            <a href="data_pilih.php" class="menu-item menu-data">
                <i class="fas fa-database"></i>
                <span>DATA PERLENGKAPAN</span>
            </a>
            <a href="pemeliharaan/pemeliharaan.php" class="menu-item menu-pemeliharaan" onclick="openMaintenancePopup()">
                <i class="fas fa-toolbox"></i>
                <span>PEMELIHARAAN</span>
            </a>
            <a href="peminjaman/peminjaman.php" class="menu-item menu-peminjaman" onclick="openLoanPopup()">
                <i class="fas fa-handshake"></i>
                <span>PEMINJAMAN</span>
            </a>
            <a href="laporan/laporan.php" class="menu-item menu-laporan">
                <i class="fas fa-file-alt"></i>
                <span>LAPORAN</span>
            </a>
        </div>
    </div>

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
</body>

</html>