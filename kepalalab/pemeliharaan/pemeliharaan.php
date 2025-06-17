<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kepalalab') {
    header("Location: ../login.html");
    exit();
}

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<script>alert('" . $_SESSION['error_message'] . "');</script>";
    unset($_SESSION['error_message']);
}

$conn = new mysqli("localhost", "root", "", "labinforpetra_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// koneksi ke database (pastikan $conn sudah ada)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_escaped = mysqli_real_escape_string($conn, $search);

$sql = "SELECT p.id_pemeliharaan, p.id_perlengkapan, p.laboratorium, p.waktu_pemeliharaan, p.waktu_pengembalian, p.deskripsi_pemeliharaan, p.status, pl.nama_perlengkapan, pl.set_komputer 
        FROM pemeliharaan p 
        JOIN perlengkapan pl ON p.id_perlengkapan = pl.id_perlengkapan";

if (!empty($search)) {
    $sql .= " WHERE pl.nama_perlengkapan LIKE '%$search_escaped%' 
                OR pl.set_komputer LIKE '%$search_escaped%' 
                OR pl.kode_seri LIKE '%$search_escaped%'";
}

$sql .= " ORDER BY 
            CASE 
                WHEN p.status = 'Dalam Proses' THEN 0 
                ELSE 1 
            END,
            p.waktu_pemeliharaan DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemeliharaan</title>
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
            padding: 30px 40px;
        }

        h2 {
            color: #1d3c74;
            border-bottom: 4px solid orange;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .button-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .action-button {
            background: linear-gradient(145deg, #3b4b79, #24345c);
            color: white;
            border: none;
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .action-button:hover {
            background: rgb(16, 32, 63);
            transform: translateY(-5px);
        }

        .button-container i {
            padding-right: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f7941d;
            color: white;
        }

        .back-button {
            background-color: lightgray;
            color: black;
            border: none;
            padding: 10px;
            margin-bottom: 24px;
            border-radius: 6px;
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .baris-dalam-proses {
            background-color: pink;
        }

        .tulisan-merah {
            color: red;
        }

        .tulisan-hijau {
            color: green;
        }

        .dropdown-container {
            display: flex;
            gap: 1rem;
            width: 100%;
            margin-bottom: 20px;
            margin-top: 20px;
            font-family: sans-serif;
            box-sizing: border-box;
        }

        .dropdown-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }

        select {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fff;
            color: #333;
            box-sizing: border-box;
        }

        select:focus {
            border-color: #0077cc;
            box-shadow: 0 0 0 3px rgba(0, 119, 204, 0.2);
            outline: none;
        }

        @media (max-width: 768px) {
            .dropdown-container {
                flex-direction: column;
            }
        }

        .btn-border {
            border-bottom: 4px solid orange;
            padding-bottom: 20px;
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
            <button class="back-button">Kembali</button>
        </a>

        <div class="button-container">
            <button class="action-button" onclick="openModal()">
                <i class="fas fa-check-circle"></i> Selesaikan Pemeliharaan
            </button>
        </div>

        <div class="button-container">
            <button class="action-button" onclick="window.location.href='pemeliharaan_perlengkapan.php'">
                <i class="fas fa-tools"></i> Pemeliharaan Perlengkapan
            </button>
            <button class="action-button" onclick="window.location.href='pemeliharaan_set.php'">
                <i class="fas fa-cogs"></i> Pemeliharaan Set
            </button>
            <button class="action-button" onclick="window.location.href='pemeliharaan_laboratorium.php'">
                <i class="fas fa-building"></i> Pemeliharaan Laboratorium
            </button>
        </div>

        <h2>Data Pemeliharaan</h2>

        <form method="GET" action="pemeliharaan.php"
            style="margin-bottom: 20px; display: flex; gap: 8px; align-items: center;">
            <input type="text" name="search" placeholder="Cari Pemeliharaan Perlengkapan..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            width: 450px;
            transition: border-color 0.3s;
        " onfocus="this.style.borderColor='#007bff'" onblur="this.style.borderColor='#ccc'">
            <button type="submit" style="
            padding: 8px 16px;
            background: linear-gradient(145deg, #3b4b79, #24345c);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        " onmouseover="this.style.backgroundColor='#0056b3'"
                onmouseout="this.style.backgroundColor='#007bff'">Cari</button>
        </form>



        <table>
            <thead>
                <tr>
                    <th>ID Pemeliharaan</th>
                    <th>Nama Perlengkapan</th>
                    <th>Laboratorium</th>
                    <th>Waktu Pemeliharaan</th>
                    <th>Waktu Pengembalian</th>
                    <th>Deskripsi Pemeliharaan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?= ($row['status'] == 'Dalam Proses') ? 'baris-dalam-proses' : ''; ?>">
                            <td><?php echo $row['id_pemeliharaan']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_perlengkapan']); ?></td>
                            <td><?php echo htmlspecialchars($row['laboratorium']); ?></td>
                            <td><?php echo htmlspecialchars($row['waktu_pemeliharaan']); ?></td>
                            <td><?php echo htmlspecialchars($row['waktu_pengembalian'] ?: 'Belum Kembali'); ?></td>
                            <td><?php echo htmlspecialchars($row['deskripsi_pemeliharaan']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">Tidak ada data pemeliharaan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <div id="myModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Pilih Pemeliharaan untuk Diselesaikan</h2>

            <form id="finishForm" method="POST" action="finish_pemeliharaan.php">
                <!-- New Kode Seri Input Section -->
                <div style="margin-bottom: 20px;">
                    <label for="kodeSeriInput" style="display:block; margin-bottom:8px; font-weight:bold;">
                        Cari dengan Kode Seri:
                    </label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="kodeSeriInput" placeholder="Masukkan kode seri perlengkapan"
                            style="flex: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px;">
                        <button type="button" onclick="checkByKodeSeri()" style="padding: 8px 16px; background: linear-gradient(145deg, #3b4b79, #24345c); 
                                    color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Check
                        </button>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="dropdown-group">
                        <label for="labSelect">Pilih Laboratorium:</label>
                        <select id="labSelect" name="laboratorium" onchange="filterPemeliharaan()">
                            <option value="">-- Pilih Laboratorium --</option>
                            <?php
                            $labResult = $conn->query("SELECT DISTINCT laboratorium FROM pemeliharaan");
                            while ($lab = $labResult->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($lab['laboratorium']) . "'>" . htmlspecialchars($lab['laboratorium']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="dropdown-group">
                        <label for="setSelect">Pilih Set Komputer (Opsional):</label>
                        <select id="setSelect" name="set_komputer" onchange="filterPemeliharaan()">
                            <option value="">-- Semua Set --</option>
                            <?php
                            $setResult = $conn->query("SELECT DISTINCT set_komputer FROM perlengkapan");
                            while ($set = $setResult->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($set['set_komputer']) . "'>" . htmlspecialchars($set['set_komputer']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Rest of the modal content remains the same -->
                <div class="btn-border">
                    <button type="button" id="checkAllButton" onclick="checkAll()"
                        style="width: 100%; padding: 10px 20px; border: 1px solid #ccc; background-color: #fff; border-radius: 4px; transition: border-color 0.3s, box-shadow 0.3s; cursor: pointer;">
                        Check Semua</button>
                </div>

                <div style="margin-top: 20px;">
                    <label for="waktu_selesai" style="display:block; margin-bottom:8px; font-weight:bold;">
                        Waktu Penyelesaian:
                    </label>
                    <input type="datetime-local" name="waktu_selesai" required
                        style="padding:8px; width:100%; border:1px solid #ddd; border-radius:4px;">
                </div>

                <div class="btn-border">
                    <button type="submit"
                        style="margin-top: 20px; width: 100%; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-check"></i> Selesaikan Pemeliharaan
                    </button>
                </div>
                <div style="clear:both;"></div>
                <table>
                    <thead>
                        <tr>
                            <th>Pilih</th>
                            <th>Nama Perlengkapan</th>
                            <th>Laboratorium</th>
                            <th>Waktu Pemeliharaan</th>
                            <th>Kode Seri</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filterResult = $conn->query("SELECT p.id_pemeliharaan, pl.nama_perlengkapan, p.laboratorium, p.waktu_pemeliharaan, pl.set_komputer, pl.kode_seri 
                                                        FROM pemeliharaan p 
                                                        JOIN perlengkapan pl ON p.id_perlengkapan = pl.id_perlengkapan 
                                                        WHERE p.status = 'Dalam Proses'");
                        while ($row = $filterResult->fetch_assoc()): ?>
                            <tr data-lab="<?= htmlspecialchars($row['laboratorium']) ?>"
                                data-set="<?= htmlspecialchars($row['set_komputer']) ?>"
                                data-kodeseri="<?= htmlspecialchars($row['kode_seri']) ?>">
                                <td><input type="checkbox" name="pemeliharaan_ids[]" value="<?= $row['id_pemeliharaan'] ?>">
                                </td>
                                <td><?= htmlspecialchars($row['nama_perlengkapan']) ?></td>
                                <td><?= htmlspecialchars($row['laboratorium']) ?></td>
                                <td><?= htmlspecialchars($row['waktu_pemeliharaan']) ?></td>
                                <td><?= htmlspecialchars($row['kode_seri']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

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

        function checkAll() {
            const checkboxes = document.querySelectorAll('input[name="pemeliharaan_ids[]"]');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
        }

        function filterPemeliharaan() {
            const selectedLab = document.getElementById('labSelect').value;
            const selectedSet = document.getElementById('setSelect').value;

            const rows = document.querySelectorAll('#finishForm table tbody tr');

            rows.forEach(row => {
                const rowLab = row.getAttribute('data-lab');
                const rowSet = row.getAttribute('data-set');
                const checkbox = row.querySelector('input[type="checkbox"]');

                if (selectedLab === "") {
                    checkbox.checked = false;
                    return;
                }

                if (rowLab === selectedLab && (selectedSet === "" || rowSet === selectedSet)) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            });
        }

        document.getElementById('finishForm').addEventListener('submit', function (e) {
            const checkboxes = document.querySelectorAll('input[name="pemeliharaan_ids[]"]:checked');

            if (checkboxes.length === 0) {
                alert('Pilih setidaknya satu pemeliharaan untuk diselesaikan!');
                e.preventDefault();
                return;
            }

            const confirmation = confirm("Apakah Anda yakin ingin menyelesaikan pemeliharaan ini?");
            if (!confirmation) {
                e.preventDefault();
            }
        });

        function checkByKodeSeri() {
            const kodeSeriInput = document.getElementById('kodeSeriInput').value.trim();
            if (!kodeSeriInput) {
                alert('Silakan masukkan kode seri terlebih dahulu!');
                return;
            }

            // Uncheck all checkboxes first
            const allCheckboxes = document.querySelectorAll('input[name="pemeliharaan_ids[]"]');
            allCheckboxes.forEach(checkbox => checkbox.checked = false);

            // Check only the matching kode seri
            const rows = document.querySelectorAll('#finishForm table tbody tr');
            let found = false;

            rows.forEach(row => {
                const rowKodeSeri = row.getAttribute('data-kodeseri');
                if (rowKodeSeri && rowKodeSeri.includes(kodeSeriInput)) {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    checkbox.checked = true;
                    found = true;
                }
            });

            if (!found) {
                alert('Tidak ditemukan pemeliharaan dengan kode seri yang cocok!');
            }
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>