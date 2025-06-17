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

$jumlah_set_komputer = 0;
$jumlah_perlengkapan = 0;
$kode_lab = isset($_GET['kode_lab']) ? $_GET['kode_lab'] : '';
$id_pengguna = $_SESSION['id_pengguna'];

if ($kode_lab == 'all') {
    // Ambil semua perlengkapan dari lab yang dapat diakses pengguna
    $stmtSet = $conn->prepare("SELECT COUNT(DISTINCT set_komputer) AS total_set FROM perlengkapan p 
                                JOIN pengguna_lab pl ON p.laboratorium = pl.kode_lab 
                                WHERE pl.id_pengguna = ?");
    $stmtSet->bind_param("i", $id_pengguna);
    $stmtSet->execute();
    $resultSet = $stmtSet->get_result();
    if ($rowSet = $resultSet->fetch_assoc()) {
        $jumlah_set_komputer = $rowSet['total_set'];
    }

    $stmtTotal = $conn->prepare("SELECT COUNT(*) AS total_perlengkapan FROM perlengkapan p 
                                    JOIN pengguna_lab pl ON p.laboratorium = pl.kode_lab 
                                    WHERE pl.id_pengguna = ?");
    $stmtTotal->bind_param("i", $id_pengguna);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    if ($rowTotal = $resultTotal->fetch_assoc()) {
        $jumlah_perlengkapan = $rowTotal['total_perlengkapan'];
    }

    $query = "SELECT * FROM perlengkapan p 
                JOIN pengguna_lab pl ON p.laboratorium = pl.kode_lab 
                WHERE pl.id_pengguna = ?";
    $params = [$id_pengguna];
    $types = "i";
} else if ($kode_lab != '') {
    // Jika kode_lab spesifik
    $stmtSet = $conn->prepare("SELECT COUNT(DISTINCT set_komputer) AS total_set FROM perlengkapan WHERE laboratorium = ?");
    $stmtSet->bind_param("s", $kode_lab);
    $stmtSet->execute();
    $resultSet = $stmtSet->get_result();
    if ($rowSet = $resultSet->fetch_assoc()) {
        $jumlah_set_komputer = $rowSet['total_set'];
    }

    $stmtTotal = $conn->prepare("SELECT COUNT(*) AS total_perlengkapan FROM perlengkapan WHERE laboratorium = ?");
    $stmtTotal->bind_param("s", $kode_lab);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    if ($rowTotal = $resultTotal->fetch_assoc()) {
        $jumlah_perlengkapan = $rowTotal['total_perlengkapan'];
    }

    $query = "SELECT * FROM perlengkapan WHERE laboratorium = ?";
    $params = [$kode_lab];
    $types = "s";
} else {
    // Jika tidak ada kode_lab yang diberikan
    $query = "SELECT * FROM perlengkapan";
    $params = [];
    $types = "";
}

// Handle search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';

    if ($kode_lab == 'all') {
        $query .= " AND (nama_perlengkapan LIKE ? OR kode_seri LIKE ? OR jenis_perlengkapan LIKE ? OR set_komputer LIKE ? OR spesifikasi LIKE ?)";
        array_push($params, $search, $search, $search, $search, $search);
        $types .= "sssss";
    } else if ($kode_lab != '') {
        $query .= " AND (nama_perlengkapan LIKE ? OR kode_seri LIKE ? OR jenis_perlengkapan LIKE ? OR set_komputer LIKE ? OR spesifikasi LIKE ?)";
        array_push($params, $search, $search, $search, $search, $search);
        $types .= "sssss";
    } else {
        $query .= " WHERE (nama_perlengkapan LIKE ? OR kode_seri LIKE ? OR jenis_perlengkapan LIKE ? OR set_komputer LIKE ? OR spesifikasi LIKE ?)";
        array_push($params, $search, $search, $search, $search, $search);
        $types = "sssss";
    }
}

// Handle filter functionality
if (isset($_GET['jenis_perlengkapan']) || isset($_GET['kondisi']) || isset($_GET['status'])) {
    $filter_jenis = isset($_GET['jenis_perlengkapan']) ? $_GET['jenis_perlengkapan'] : '';
    $filter_kondisi = isset($_GET['kondisi']) ? $_GET['kondisi'] : '';
    $filter_status = isset($_GET['status']) ? $_GET['status'] : '';

    if ($kode_lab == 'all') {
        // Filtering untuk semua perlengkapan
        if ($filter_jenis != '') {
            $query .= " AND jenis_perlengkapan = ?";
            $params[] = $filter_jenis;
            $types .= "s";
        }
        if ($filter_kondisi != '') {
            $query .= " AND kondisi = ?";
            $params[] = $filter_kondisi;
            $types .= "s";
        }
        if ($filter_status != '') {
            $query .= " AND status = ?";
            $params[] = $filter_status;
            $types .= "s";
        }
    } else if ($kode_lab != '') {
        // Filtering untuk lab spesifik
        if ($filter_jenis != '') {
            $query .= " AND jenis_perlengkapan = ?";
            $params[] = $filter_jenis;
            $types .= "s";
        }
        if ($filter_kondisi != '') {
            $query .= " AND kondisi = ?";
            $params[] = $filter_kondisi;
            $types .= "s";
        }
        if ($filter_status != '') {
            $query .= " AND status = ?";
            $params[] = $filter_status;
            $types .= "s";
        }
    }
}

if ($types) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Perlengkapan</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f7941d;
            color: white;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .button-container a {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .back-button {
            background-color: gray;
            /* Biru untuk tombol Kembali */
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            /* Jarak antara tombol */
        }

        .add-button {
            background-color: #4CAF50;
            /* Hijau untuk tombol Tambah Pengguna */
        }

        .edit-button {
            background-color: #FFC107;
            /* Kuning untuk tombol Edit */
            color: black;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
        }

        .edit-button:hover {
            transform: translateY(-3px);
            /* Mengangkat tombol sedikit */
            background-color: rgb(218, 165, 6);
            /* Mengelap sedikit */
        }

        .delete-button {
            background-color: #F44336;
            /* Merah untuk tombol Delete */
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
        }

        .delete-button:hover {
            transform: translateY(-3px);
            /* Mengangkat tombol sedikit */
            background-color: rgb(207, 57, 47);
            /* Mengelap sedikit */
        }

        /* Pop-up konfirmasi */
        .confirm-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 300px;
        }

        .confirm-popup button {
            margin-top: 10px;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .confirm-popup .confirm {
            background-color: #F44336;
            color: white;
        }

        .confirm-popup .cancel {
            background-color: #007BFF;
            color: white;
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

        .baris-tidak-bisa-dipakai {
            background-color: pink;
        }

        .status-tulisan-merah {
            color: red;
        }

        .status-tulisan-hijau {
            color: green;
        }

        .kondisi-pemeliharaan {
            color: blue;
        }

        .kondisi-peminjaman {
            color: magenta;
        }

        select:focus {
            outline: none;
            border-color: #f89c1c;
            box-shadow: 0 0 0 2px rgba(248, 156, 28, 0.2);
        }

        h2 {
            color: #1e2a4a;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 4px;
        }

        .title-underline {
            border-bottom: 3px solid #f89c1c;
            width: 100%;
            margin-bottom: 16px;
        }

        .filter-search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-form,
        .search-form {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .filter-form select,
        .search-form input {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-form button,
        .search-form button {
            background-color: #1e2a4a;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .filter-form button:hover,
        .search-form button:hover {
            background-color: rgb(19, 26, 45);
        }

        .button-filter-search {
            padding: 8px 16px;
            background-color: #1e2a4a;
            color: white;
            border: none;
            border-radius: 4px;
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
            <form id="logoutForm" action="../php/logout.php" method="post">
                <button type="button" onclick="confirmLogout()">Logout</button>
            </form>
        </div>
    </header>

    <div class="menu-section">
        <div class="button-container">
            <a href="data_pilih.php" class="back-button">Kembali</a>
            <a href="perlengkapan_tambah.php" class="add-button">Tambah Perlengkapan</a>
        </div>
        <?php if (isset($kode_lab)): ?>
            <h2>Menampilkan perlengkapan dari laboratorium: <?= htmlspecialchars($kode_lab); ?></h2>
        <?php endif; ?>
        <?php if (isset($kode_lab)): ?>
            <p><strong>Jumlah Set Komputer:</strong> <?= $jumlah_set_komputer; ?></p>
            <p><strong>Jumlah Total Perlengkapan:</strong> <?= $jumlah_perlengkapan; ?></p>
        <?php endif; ?>

        <div class="filter-search-container">
            <form method="GET" class="filter-form">
                <input type="hidden" name="kode_lab" value="<?= htmlspecialchars($kode_lab); ?>">
                <input type="hidden" name="search"
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                <select name="jenis_perlengkapan">
                    <option value="">Semua Jenis</option>
                    <option value="Main Hardware" <?= isset($_GET['jenis_perlengkapan']) && $_GET['jenis_perlengkapan'] == 'Main Hardware' ? 'selected' : ''; ?>>Main Hardware</option>
                    <option value="Cables & Connector" <?= isset($_GET['jenis_perlengkapan']) && $_GET['jenis_perlengkapan'] == 'Cables & Connector' ? 'selected' : ''; ?>>Cables & Connector
                    </option>
                    <option value="Networking Device" <?= isset($_GET['jenis_perlengkapan']) && $_GET['jenis_perlengkapan'] == 'Networking Device' ? 'selected' : ''; ?>>Networking Device
                    </option>
                    <option value="Fasilitas Ruangan" <?= isset($_GET['jenis_perlengkapan']) && $_GET['jenis_perlengkapan'] == 'Fasilitas Ruangan' ? 'selected' : ''; ?>>Fasilitas Ruangan
                    </option>
                </select>

                <select name="kondisi">
                    <option value="">Semua Kondisi</option>
                    <option value="Tersedia" <?= isset($_GET['kondisi']) && $_GET['kondisi'] == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="Pemeliharaan" <?= isset($_GET['kondisi']) && $_GET['kondisi'] == 'Pemeliharaan' ? 'selected' : ''; ?>>Pemeliharaan</option>
                    <option value="Peminjaman" <?= isset($_GET['kondisi']) && $_GET['kondisi'] == 'Peminjaman' ? 'selected' : ''; ?>>Peminjaman</option>
                </select>

                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="Bisa Dipakai" <?= isset($_GET['status']) && $_GET['status'] == 'Bisa Dipakai' ? 'selected' : ''; ?>>Bisa Dipakai</option>
                    <option value="Tidak Bisa Dipakai" <?= isset($_GET['status']) && $_GET['status'] == 'Tidak Bisa Dipakai' ? 'selected' : ''; ?>>Tidak Bisa Dipakai</option>
                </select>

                <button type="submit" class="button-filter-search">Terapkan Filter</button>
            </form>

            <form method="GET" class="search-form">
                <input type="hidden" name="kode_lab" value="<?= htmlspecialchars($kode_lab); ?>">
                <input type="hidden" name="jenis_perlengkapan"
                    value="<?= isset($_GET['jenis_perlengkapan']) ? htmlspecialchars($_GET['jenis_perlengkapan']) : '' ?>">
                <input type="hidden" name="kondisi"
                    value="<?= isset($_GET['kondisi']) ? htmlspecialchars($_GET['kondisi']) : '' ?>">
                <input type="hidden" name="status"
                    value="<?= isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '' ?>">

                <input type="text" name="search" placeholder="Cari perlengkapan..." style="padding-right: 80px;"
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="button-filter-search">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if (isset($_GET['search'])): ?>
                    <a href="?kode_lab=<?= htmlspecialchars($kode_lab) ?><?php
                      // Preserve other filters when resetting search
                        if (isset($_GET['jenis_perlengkapan']))
                            echo '&jenis_perlengkapan=' . htmlspecialchars($_GET['jenis_perlengkapan']);
                        if (isset($_GET['kondisi']))
                            echo '&kondisi=' . htmlspecialchars($_GET['kondisi']);
                        if (isset($_GET['status']))
                            echo '&status=' . htmlspecialchars($_GET['status']);
                        ?>" class="reset-button">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Seri</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Set Komputer</th>
                    <th>Spesifikasi</th>
                    <th>Laboratorium</th>
                    <th>Kondisi</th>
                    <th>Status</th>
                    <th>Waktu Masuk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?= ($row['status'] == 'Tidak Bisa Dipakai') ? 'baris-tidak-bisa-dipakai' : ''; ?>">
                            <td><?= $row['id_perlengkapan']; ?></td>
                            <td><?= $row['kode_seri']; ?></td>
                            <td><?= $row['nama_perlengkapan']; ?></td>
                            <td><?= $row['jenis_perlengkapan']; ?></td>
                            <td><?= $row['set_komputer']; ?></td>
                            <td><?= $row['spesifikasi']; ?></td>
                            <td><?= $row['laboratorium']; ?></td>
                            <td class="<?php
                            if ($row['kondisi'] == 'Pemeliharaan') {
                                echo 'kondisi-pemeliharaan';
                            } elseif ($row['kondisi'] == 'Peminjaman') {
                                echo 'kondisi-peminjaman';
                            }
                            ?>">
                                <?= $row['kondisi']; ?>
                            </td>
                            <td class="<?php
                            if ($row['status'] == 'Tidak Bisa Dipakai') {
                                echo 'status-tulisan-merah';
                            } elseif ($row['status'] == 'Bisa Dipakai') {
                                echo 'status-tulisan-hijau';
                            }
                            ?>">
                                <?= $row['status']; ?>
                            </td>
                            <td><?= $row['waktu_masuk']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="perlengkapan_edit.php?id=<?= $row['id_perlengkapan']; ?>"
                                        class="edit-button">Edit</a>
                                    <a href="#" class="delete-button"
                                        onclick="confirmDelete('<?= $row['nama_perlengkapan']; ?>', <?= $row['id_perlengkapan']; ?>)">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Tidak ada data perlengkapan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <!-- Overlay dan Pop-up -->
    <div class="overlay" id="overlay"></div>
    <div class="confirm-popup" id="confirmPopup">
        <p>Apakah Anda yakin ingin menghapus perlengkapan <span id="itemName"></span>?</p>
        <button class="confirm" onclick="deleteItem()">Ya, Hapus</button>
        <button class="cancel" onclick="closeConfirmPopup()">Batal</button>
    </div>

    <script>
        let itemIdToDelete = null;

        function togglePopup() {
            const popup = document.getElementById("userPopup");
            popup.style.display = popup.style.display === "block" ? "none" : "block";
        }

        function confirmDelete(name, id) {
            itemIdToDelete = id;
            document.getElementById("itemName").textContent = name;
            document.getElementById("confirmPopup").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }

        function closeConfirmPopup() {
            itemIdToDelete = null;
            document.getElementById("confirmPopup").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }

        function deleteItem() {
            if (itemIdToDelete !== null) {
                window.location.href = `perlengkapan_delete.php?id=${itemIdToDelete}`;
            }
        }

        function confirmLogout() {
            if (confirm("Yakin ingin logout?")) {
                document.getElementById("logoutForm").submit();
            }
        }

        window.onclick = function (event) {
            const popup = document.getElementById("userPopup");
            if (!event.target.closest(".user-box") && !event.target.closest("#userPopup")) {
                popup.style.display = "none";
            }
        };
    </script>
</body>

</html>