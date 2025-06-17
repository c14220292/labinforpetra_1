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
// Ambil daftar laboratorium yang dapat diakses oleh pengguna
$sql_labs = "SELECT l.kode_lab, l.nama_lab 
                FROM pengguna_lab pl 
                JOIN laboratorium l ON pl.kode_lab = l.kode_lab 
                WHERE pl.id_pengguna = $id_pengguna";

$result_labs = $conn->query($sql_labs);


// Proses tambah perlengkapan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_seri = $_POST['kode_seri'];
    $nama_perlengkapan = $_POST['nama_perlengkapan'];
    $jenis_perlengkapan = $_POST['jenis_perlengkapan'];
    $set_komputer = $_POST['set_komputer'];
    $spesifikasi = $_POST['spesifikasi'];
    $laboratorium = $_POST['laboratorium'];
    $kondisi = $_POST['kondisi'];
    $status = $_POST['status'];

    $sql = "INSERT INTO perlengkapan (kode_seri, nama_perlengkapan, jenis_perlengkapan, set_komputer, spesifikasi, laboratorium, kondisi, status, waktu_masuk) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissss", $kode_seri, $nama_perlengkapan, $jenis_perlengkapan, $set_komputer, $spesifikasi, $laboratorium, $kondisi, $status);

    if ($stmt->execute()) {
        // Masukkan laporan
        $jenis_laporan = "Tambah Perlengkapan";
        $user = $_SESSION['email']; // atau bisa juga $_SESSION['id_pengguna'] kalau 'user' disimpan berupa ID
        $keterangan = "Perlengkapan ($nama_perlengkapan - $set_komputer) telah ditambahkan";
        $waktu_laporan = date("Y-m-d H:i:s");

        $sql_laporan = "INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt_laporan = $conn->prepare($sql_laporan);
        $stmt_laporan->bind_param("sssss", $jenis_laporan, $user, $keterangan, $laboratorium, $waktu_laporan);
        $stmt_laporan->execute();

        // Redirect setelah input
        header("Location: perlengkapan.php?message=Perlengkapan berhasil ditambahkan");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Ambil daftar laboratorium untuk dropdown
$lab_result = $conn->query("SELECT kode_lab, nama_lab FROM laboratorium");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Perlengkapan</title>
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

        main {
            padding: 30px 40px;
        }

        h2 {
            color: #1d3c74;
            border-bottom: 4px solid orange;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            max-width: 500px;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input,
        select,
        textarea {
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button[type="submit"] {
            background-color: #f26522;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .back-button {
            background-color: lightgray;
            color: black;
            border: none;
            padding: 10px;
            margin-bottom: 24px;
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
            <form id="logoutForm" action="../php/logout.php" method="post">
                <button type="button" onclick="confirmLogout()">Logout</button>
            </form>
        </div>
    </header>

    <main>
        <a href="data_pilih.php">
            <button class="back-button">Kembali</button>
        </a>

        <h2>Tambah Perlengkapan Baru</h2>
        <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menambahkan data perlengkapan ini?');">
            <label for="kode_seri">Kode Seri</label>
            <input type="text" id="kode_seri" name="kode_seri" required>

            <label for="nama_perlengkapan">Nama Perlengkapan</label>
            <input type="text" id="nama_perlengkapan" name="nama_perlengkapan" required>

            <label for="jenis_perlengkapan">Jenis Perlengkapan</label>
            <select id="jenis_perlengkapan" name="jenis_perlengkapan" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="Main Hardware">Main Hardware</option>
                <option value="Cables & Connector">Cables & Connector</option>
                <option value="Networking Device">Networking Device</option>
                <option value="Fasilitas Ruangan">Fasilitas Ruangan</option>
            </select>

            <label for="set_komputer">Set Komputer (isi angka jika terkait)</label>
            <input type="number" id="set_komputer" name="set_komputer" min="0">

            <label for="spesifikasi">Spesifikasi</label>
            <textarea id="spesifikasi" name="spesifikasi" rows="4"></textarea>

            <label for="laboratorium">Laboratorium:</label>
            <select name="laboratorium" id="laboratorium" required>
                <option value="">-- Pilih Laboratorium --</option>
                <?php if ($result_labs->num_rows > 0): ?>
                    <?php while ($lab = $result_labs->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($lab['kode_lab']); ?>">
                            <?php echo htmlspecialchars($lab['kode_lab'] . " - " . $lab['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">Tidak ada laboratorium yang tersedia</option>
                <?php endif; ?>
            </select>


            <label for="kondisi">Kondisi</label>
            <select id="kondisi" name="kondisi" required>
                <option value="Tersedia">Tersedia</option>
                <option value="Pemeliharaan">Pemeliharaan</option>
                <option value="Peminjaman">Peminjaman</option>
            </select>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Bisa Dipakai">Bisa Dipakai</option>
                <option value="Tidak Bisa Dipakai">Tidak Bisa Dipakai</option>
            </select>

            <button type="submit">Tambah Perlengkapan</button>
        </form>
    </main>

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

<?php
$conn->close();
?>