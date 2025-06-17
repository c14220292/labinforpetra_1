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

// Ambil email pengguna dari tabel pengguna
$sql_email = "SELECT email FROM pengguna WHERE id_pengguna = ?";
$stmt = $conn->prepare($sql_email);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result_email = $stmt->get_result();
$email_row = $result_email->fetch_assoc();
$email_peminjam = $email_row['email'];

// Ambil daftar laboratorium yang dapat diakses oleh kepala lab
$sql_laboratorium = "SELECT l.kode_lab, l.nama_lab FROM laboratorium l 
                        JOIN pengguna_lab pl ON l.kode_lab = pl.kode_lab 
                        WHERE pl.id_pengguna = ?";
$stmt = $conn->prepare($sql_laboratorium);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result_laboratorium = $stmt->get_result();

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $laboratorium = $_POST['laboratorium'];
    $email_peminjam = $_POST['email_peminjam'];
    $waktu_peminjaman = $_POST['waktu_peminjaman'];
    $waktu_pengembalian = $_POST['waktu_pengembalian'] ?: null;

    // Cek apakah email ada di tabel pengguna
    $check_email_sql = "SELECT * FROM pengguna WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email_peminjam);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();

    if ($check_email_result->num_rows > 0) {
        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // Ambil semua perlengkapan yang tersedia di laboratorium yang dipilih
            $sql_perlengkapan = "SELECT id_perlengkapan, nama_perlengkapan 
                                FROM perlengkapan 
                                WHERE laboratorium = ? AND kondisi = 'Tersedia' AND status = 'Bisa Dipakai'";
            $stmt_perlengkapan = $conn->prepare($sql_perlengkapan);
            $stmt_perlengkapan->bind_param("s", $laboratorium);
            $stmt_perlengkapan->execute();
            $result_perlengkapan = $stmt_perlengkapan->get_result();

            $perlengkapan_dipinjam = [];
            $berhasil_dipinjam = 0;

            // Insert peminjaman untuk setiap perlengkapan yang tersedia
            while ($perlengkapan = $result_perlengkapan->fetch_assoc()) {
                $insert_sql = "INSERT INTO peminjaman (id_perlengkapan, laboratorium, email_peminjaman, 
                                waktu_peminjaman, waktu_pengembalian, status) 
                                VALUES (?, ?, ?, ?, ?, 'Dalam Proses')";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param(
                    "issss",
                    $perlengkapan['id_perlengkapan'],
                    $laboratorium,
                    $email_peminjam,
                    $waktu_peminjaman,
                    $waktu_pengembalian
                );

                if ($insert_stmt->execute()) {
                    // Update kondisi perlengkapan menjadi 'Peminjaman'
                    $update_sql = "UPDATE perlengkapan 
                                SET kondisi = 'Peminjaman', status = 'Tidak Bisa Dipakai' 
                                WHERE id_perlengkapan = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $perlengkapan['id_perlengkapan']);
                    $update_stmt->execute();

                    $perlengkapan_dipinjam[] = $perlengkapan['nama_perlengkapan'];
                    $berhasil_dipinjam++;
                }
            }

            if ($berhasil_dipinjam > 0) {
                // Format waktu untuk laporan
                $waktu_peminjaman_formatted = date('d-m-Y H:i', strtotime($waktu_peminjaman));

                // Tambahkan ke tabel laporan
                $jenis_laporan = "Peminjaman";
                $user = $_SESSION['email'];
                $keterangan = "Laboratorium " . $laboratorium . " dipinjam oleh " . $email_peminjam .
                    " pada " . $waktu_peminjaman_formatted . ". " .
                    "Total perlengkapan dipinjam: " . $berhasil_dipinjam . " item. " .
                    "Daftar perlengkapan: " . implode(", ", $perlengkapan_dipinjam);
                $waktu_laporan = date('Y-m-d H:i:s');

                $laporan_sql = "INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) 
                                VALUES (?, ?, ?, ?, ?)";
                $laporan_stmt = $conn->prepare($laporan_sql);
                $laporan_stmt->bind_param("sssss", $jenis_laporan, $user, $keterangan, $laboratorium, $waktu_laporan);
                $laporan_stmt->execute();

                // Commit transaksi jika berhasil
                $conn->commit();

                $daftar_perlengkapan = implode(", ", $perlengkapan_dipinjam);
                $_SESSION['success_message'] = "Peminjaman berhasil ditambahkan untuk laboratorium $laboratorium.";
                header("Location: peminjaman.php");
                exit();
            } else {
                // Rollback jika tidak ada perlengkapan yang bisa dipinjam
                $conn->rollback();
                echo "<script>
                        alert('Tidak ada perlengkapan yang tersedia untuk dipinjam di laboratorium ini.');
                        </script>";
            }

        } catch (Exception $e) {
            // Rollback jika terjadi error
            $conn->rollback();
            echo "<script>
                    alert('Gagal menambahkan peminjaman: " . $e->getMessage() . "');
                    </script>";
        }
    } else {
        echo "<script>
                alert('Email tidak terdaftar. Silakan masukkan email yang valid.');
                </script>";
    }
}

// Function untuk menampilkan preview perlengkapan
function getPreviewPerlengkapan($conn, $kode_lab)
{
    $sql = "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN kondisi = 'Tersedia' AND status = 'Bisa Dipakai' THEN 1 ELSE 0 END) as tersedia
            FROM perlengkapan WHERE laboratorium = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kode_lab);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Laboratorium</title>
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
            display: flex;
            gap: 20px;
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
            gap: 15px;
            margin-right: 48px;
        }

        #perlengkapanList {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f7941d;
            text-align: center;
            color: white;
        }

        input,
        select {
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

        .info-box {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            color: #0c5460;
        }

        .info-box p {
            margin: 5px 0;
            color: #0c5460;
        }

        .preview-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
            display: none;
        }

        .preview-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }

        .warning-text {
            color: #d63031;
            font-size: 14px;
            font-style: italic;
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
        <div style="flex: 1;">
            <a href="peminjaman.php">
                <button class="back-button">Kembali</button>
            </a>

            <form method="POST" action="" id="peminjamanForm">
                <h2>Peminjaman Laboratorium</h2>
                <label for="laboratorium">Laboratorium</label>
                <select id="laboratorium" name="laboratorium" required onchange="fetchPerlengkapan()">
                    <option value="">-- Pilih Laboratorium --</option>
                    <?php
                    // Reset result pointer untuk loop kedua 
                    $result_laboratorium->data_seek(0);
                    while ($lab = $result_laboratorium->fetch_assoc()):
                        $preview = getPreviewPerlengkapan($conn, $lab['kode_lab']);
                        ?>
                        <option value="<?php echo $lab['kode_lab']; ?>" data-total="<?php echo $preview['total']; ?>"
                            data-tersedia="<?php echo $preview['tersedia']; ?>">
                            <?php echo htmlspecialchars($lab['kode_lab'] . " - " . $lab['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div class="preview-box" id="previewBox">
                    <h4><i class="fas fa-list"></i> Preview Perlengkapan</h4>
                    <p id="previewText"></p>
                </div>

                <label for="email_peminjam">Email Peminjam</label>
                <input type="email" id="email_peminjam" name="email_peminjam" required>

                <label for="waktu_peminjaman">Waktu Peminjaman</label>
                <input type="datetime-local" id="waktu_peminjaman" name="waktu_peminjaman" required>

                <label for="waktu_pengembalian">Waktu Pengembalian (opsional)</label>
                <input type="datetime-local" id="waktu_pengembalian" name="waktu_pengembalian">

                <button type="submit" onclick="return confirmPeminjaman()">
                    <i class="fas fa-plus-circle"></i> Pinjam Semua Perlengkapan Lab
                </button>
            </form>
        </div>
        <div id="perlengkapanList" style="flex: 1; margin-top: 20px;">
            <table id="perlengkapanTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Kode Seri</th>
                        <th>Nama Perlengkapan</th>
                        <th>Jenis Perlengkapan</th>
                        <th>Set Komputer</th>
                    </tr>
                </thead>
                <tbody id="perlengkapanItems"></tbody>
            </table>
        </div>
    </main>

    <script>
        function fetchPerlengkapan() {
            const laboratorium = document.getElementById("laboratorium").value;
            const perlengkapanItems = document.getElementById("perlengkapanItems");

            perlengkapanItems.innerHTML = '';

            if (laboratorium) {
                fetch(`get_perlengkapan2.php?laboratorium=${laboratorium}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(perlengkapan => {
                        perlengkapan.forEach(item => {
                            const row = document.createElement("tr");
                            row.innerHTML = `
                        <td>${item.kode_seri}</td>
                        <td>${item.nama_perlengkapan}</td>
                        <td>${item.jenis_perlengkapan}</td>
                        <td>${item.set_komputer}</td>
                    `;
                            perlengkapanItems.appendChild(row);
                        });
                    })
                    .catch(error => console.error('Error fetching perlengkapan:', error));
            }
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
        
        function confirmPeminjaman() {
            const laboratorium = document.getElementById("laboratorium").value;
            if (!laboratorium) {
                alert("Silakan pilih laboratorium terlebih dahulu!");
                return false;
            }

            const selectedOption = document.getElementById("laboratorium").options[document.getElementById("laboratorium").selectedIndex];
            const tersedia = selectedOption.getAttribute('data-tersedia');

            if (tersedia == 0) {
                alert("Tidak ada perlengkapan yang tersedia untuk dipinjam di laboratorium ini!");
                return false;
            }

            return confirm(`Anda akan meminjam SEMUA perlengkapan (${tersedia} item) yang tersedia di laboratorium ini.\n\nApakah Anda yakin?`);
        }

    </script>
</body>

</html>

<?php
$conn->close();
?>