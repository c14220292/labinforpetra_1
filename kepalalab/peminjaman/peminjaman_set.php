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
    $set_komputer = $_POST['set_komputer'];
    $laboratorium = $_POST['laboratorium'];
    $email_peminjam = $_POST['email_peminjam']; // Ambil email dari input
    $waktu_peminjaman = $_POST['waktu_peminjaman'];
    $waktu_pengembalian = $_POST['waktu_pengembalian'] ?: null;

    // Cek apakah email ada di tabel pengguna
    $check_email_sql = "SELECT * FROM pengguna WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email_peminjam);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();

    if ($check_email_result->num_rows > 0) {
        // Ambil perlengkapan berdasarkan set_komputer dan laboratorium
        $sql_perlengkapan = "SELECT id_perlengkapan, nama_perlengkapan FROM perlengkapan 
                                WHERE set_komputer = ? AND laboratorium = ?";
        $stmt = $conn->prepare($sql_perlengkapan);
        $stmt->bind_param("is", $set_komputer, $laboratorium);
        $stmt->execute();
        $result_perlengkapan = $stmt->get_result();

        // Simpan daftar perlengkapan yang dipinjam untuk laporan
        $perlengkapan_dipinjam = [];

        // Simpan peminjaman untuk setiap perlengkapan yang ditemukan
        while ($row = $result_perlengkapan->fetch_assoc()) {
            $id_perlengkapan = $row['id_perlengkapan'];
            $perlengkapan_dipinjam[] = $row['nama_perlengkapan'];

            // Insert peminjaman ke tabel
            $insert_sql = "INSERT INTO peminjaman (id_perlengkapan, laboratorium, email_peminjaman, 
                        waktu_peminjaman, waktu_pengembalian, status) 
                        VALUES (?, ?, ?, ?, ?, 'Dalam Proses')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param(
                "issss",
                $id_perlengkapan,
                $laboratorium,
                $email_peminjam,
                $waktu_peminjaman,
                $waktu_pengembalian
            );
            $insert_stmt->execute();

            // Update status dan kondisi perlengkapan
            $update_sql = "UPDATE perlengkapan 
                        SET kondisi = 'Peminjaman', status = 'Tidak Bisa Dipakai' 
                        WHERE id_perlengkapan = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id_perlengkapan);
            $update_stmt->execute();
        }

        // Tambahkan ke tabel laporan
        $jenis_laporan = "Peminjaman";
        $user = $_SESSION['email']; // pengguna yang login

        // Format datetime for display
        $waktu_peminjaman_formatted = date('d-m-Y H:i', strtotime($waktu_peminjaman));

        $keterangan = "Set Komputer " . $set_komputer . " di laboratorium " . $laboratorium .
            " dipinjam oleh " . $email_peminjam . " pada " . $waktu_peminjaman_formatted .
            ". Perlengkapan yang dipinjam: " . implode(", ", $perlengkapan_dipinjam);
        $waktu_laporan = date('Y-m-d H:i:s');

        $laporan_sql = "INSERT INTO laporan (jenis_laporan, user, keterangan, laboratorium, waktu_laporan) 
                    VALUES (?, ?, ?, ?, ?)";
        $laporan_stmt = $conn->prepare($laporan_sql);
        $laporan_stmt->bind_param("sssss", $jenis_laporan, $user, $keterangan, $laboratorium, $waktu_laporan);
        $laporan_stmt->execute();

        $_SESSION['success_message'] = "Peminjaman berhasil ditambahkan untuk set $set_komputer dan laboratorium $laboratorium.";
        header("Location: peminjaman.php");
        exit();
    } else {
        echo "<script>
                alert('Email tidak terdaftar. Silakan masukkan email yang valid.');
                </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Set Komputer</title>
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
        select,
        textarea {
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
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
            <a href="peminjaman.php">
                <button class="back-button">Kembali</button>
            </a>

            <form method="POST" action="" id="peminjamanForm">
                <h2>Pilih Laboratorium dan Set Komputer</h2>
                <label for="laboratorium">Laboratorium</label>
                <select id="laboratorium" name="laboratorium" required onchange="fetchSetKomputer()">
                    <option value="">-- Pilih Laboratorium --</option>
                    <?php while ($lab = $result_laboratorium->fetch_assoc()): ?>
                        <option value="<?php echo $lab['kode_lab']; ?>">
                            <?php echo htmlspecialchars($lab['kode_lab'] . " - " . $lab['nama_lab']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="set_komputer">Set Komputer</label>
                <select id="set_komputer" name="set_komputer" required onchange="fetchPerlengkapan()">
                    <option value="">-- Pilih Set Komputer --</option>
                    <!-- Set komputer akan diisi melalui AJAX -->
                </select>

                <label for="email_peminjam">Email Peminjam</label>
                <input type="email" id="email_peminjam" name="email_peminjam" required>

                <label for="waktu_peminjaman">Waktu Peminjaman</label>
                <input type="datetime-local" id="waktu_peminjaman" name="waktu_peminjaman" required>

                <label for="waktu_pengembalian">Waktu Pengembalian (kosongkan jika belum ada)</label>
                <input type="datetime-local" id="waktu_pengembalian" name="waktu_pengembalian">

                <button type="submit">Tambah Peminjaman</button>
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
        function fetchSetKomputer() {
            const laboratorium = document.getElementById("laboratorium").value;
            const setKomputerSelect = document.getElementById("set_komputer");

            setKomputerSelect.innerHTML = '<option value="">-- Pilih Set Komputer --</option>';

            if (laboratorium) {
                fetch(`get_set_komputer.php?laboratorium=${laboratorium}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        data.forEach(set => {
                            const option = document.createElement("option");
                            option.value = set;
                            option.textContent = `Set ${set}`;
                            setKomputerSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching set komputer:', error));
            }
        }

        function fetchPerlengkapan() {
            const laboratorium = document.getElementById("laboratorium").value;
            const setKomputer = document.getElementById("set_komputer").value;
            const perlengkapanItems = document.getElementById("perlengkapanItems");

            perlengkapanItems.innerHTML = '';

            if (laboratorium && setKomputer) {
                fetch(`get_perlengkapan.php?laboratorium=${laboratorium}&set_komputer=${setKomputer}`)
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
        document.getElementById('peminjamanForm').addEventListener('submit', function (e) {
            const confirmation = confirm("Apakah Anda yakin ingin melakukan peminjaman ini?");
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