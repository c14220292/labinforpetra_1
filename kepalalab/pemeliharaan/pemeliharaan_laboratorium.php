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

$id_pengguna = $_SESSION['id_pengguna'];

$sql_email = "SELECT email FROM pengguna WHERE id_pengguna = ?";
$stmt = $conn->prepare($sql_email);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result_email = $stmt->get_result();
$email_row = $result_email->fetch_assoc();
$user_email = $email_row['email'];

$sql_laboratorium = "SELECT l.kode_lab, l.nama_lab FROM laboratorium l 
                        JOIN pengguna_lab pl ON l.kode_lab = pl.kode_lab 
                        WHERE pl.id_pengguna = ?";
$stmt = $conn->prepare($sql_laboratorium);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result_laboratorium = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $laboratorium = $_POST['laboratorium'];
    $waktu_pemeliharaan = $_POST['waktu_pemeliharaan'];
    $waktu_pengembalian = $_POST['waktu_pengembalian'] ?: null; 
    $deskripsi_pemeliharaan = $_POST['deskripsi_pemeliharaan'];

    $sql_perlengkapan = "SELECT id_perlengkapan FROM perlengkapan WHERE laboratorium = ?";
    $stmt = $conn->prepare($sql_perlengkapan);
    $stmt->bind_param("s", $laboratorium);
    $stmt->execute();
    $result_perlengkapan = $stmt->get_result();

    $perlengkapan_dipelihara = [];

    while ($row = $result_perlengkapan->fetch_assoc()) {
        $id_perlengkapan = $row['id_perlengkapan'];
        $perlengkapan_dipelihara[] = $id_perlengkapan;

        $insert_sql = "INSERT INTO pemeliharaan (id_perlengkapan, laboratorium, waktu_pemeliharaan, waktu_pengembalian, deskripsi_pemeliharaan, status) 
                        VALUES (?, ?, ?, ?, ?, 'Dalam Proses')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("issss", $id_perlengkapan, $laboratorium, $waktu_pemeliharaan, $waktu_pengembalian, $deskripsi_pemeliharaan);
        $insert_stmt->execute();

        $update_sql = "UPDATE perlengkapan 
                        SET kondisi = 'Pemeliharaan', status = 'Tidak Bisa Dipakai' 
                        WHERE id_perlengkapan = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $id_perlengkapan);
        $update_stmt->execute();
    }

    $jenis_laporan = "Pemeliharaan";
    $waktu_laporan = date('Y-m-d H:i:s');
    $keterangan = "Laboratorium $laboratorium dimasukkan ke dalam pemeliharaan pada " . date('d-m-Y H:i', strtotime($waktu_pemeliharaan)) . ". " .
        "Perlengkapan: " . implode(", ", $perlengkapan_dipelihara);

    $laporan_sql = "INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) 
                    VALUES (?, ?, ?, ?, ?)";
    $laporan_stmt = $conn->prepare($laporan_sql);
    $laporan_stmt->bind_param("sssss", $jenis_laporan, $user_email, $keterangan, $laboratorium, $waktu_laporan);
    $laporan_stmt->execute();

    $_SESSION['success_message'] = "Pemeliharaan berhasil ditambahkan untuk laboratorium $laboratorium.";
    header("Location: pemeliharaan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeliharaan Laboratorium</title>
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
            align-self: start;
            margin-top: 10px;
            background-color: #f26522;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            align-self: start;
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
            <a href="pemeliharaan.php">
                <button class="back-button">Kembali</button>
            </a>

            <form method="POST" action="" id="pemeliharaanForm">
                <h2>Pilih Laboratorium untuk Pemeliharaan</h2>
                <label for="laboratorium">Laboratorium</label>
                <select id="laboratorium" name="laboratorium" required onchange="fetchPerlengkapan()">
                    <option value="">-- Pilih Laboratorium --</option>
                    <?php while ($lab = $result_laboratorium->fetch_assoc()): ?>
                        <option value="<?php echo $lab['kode_lab']; ?>">
                            <?php echo htmlspecialchars($lab['kode_lab'] . " - " . $lab['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="waktu_pemeliharaan">Waktu Pemeliharaan</label>
                <input type="datetime-local" id="waktu_pemeliharaan" name="waktu_pemeliharaan" required>

                <label for="waktu_pengembalian">Waktu Pengembalian (kosongkan jika belum ada)</label>
                <input type="datetime-local" id="waktu_pengembalian" name="waktu_pengembalian">

                <label for="deskripsi_pemeliharaan">Deskripsi Pemeliharaan</label>
                <textarea id="deskripsi_pemeliharaan" name="deskripsi_pemeliharaan" rows="4" required></textarea>

                <button type="submit">Tambah Pemeliharaan</button>
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
                                <td>${item.set_komputer}
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
        
        document.getElementById('pemeliharaanForm').addEventListener('submit', function (e) {
            const confirmation = confirm("Apakah Anda yakin ingin melakukan pemeliharaan ini?");
            if (!confirmation) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>