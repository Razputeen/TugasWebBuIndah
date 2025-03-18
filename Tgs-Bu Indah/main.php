<?php
session_start();

// Redirect kalau belum login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "lamp_control");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data user dari sesi
$user = $_SESSION['user'];
$queryUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
$queryUser->bind_param("s", $user);
$queryUser->execute();
$resultUser = $queryUser->get_result();
$userData = $resultUser->fetch_assoc();

// Atur room_code, kalau belum ada pakai ID user
$room_code = $userData['room_code'] ?: $userData['id'];
$conn->query("UPDATE users SET room_code='$room_code' WHERE username='$user'");

// Proses form jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Tambah lampu baru
        $name = $_POST['name'];
        $serial_code = $_POST['serial_code'];

        $stmt = $conn->prepare("INSERT INTO lamps (name, serial_code, room_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $serial_code, $room_code);
        $stmt->execute();

    } elseif (isset($_POST['toggle'])) {
        // Ubah status lampu
        $id = $_POST['id'];
        $status = ($_POST['status'] == 'ON') ? 'OFF' : 'ON';

        $stmt = $conn->prepare("UPDATE lamps SET status=? WHERE id=? AND room_code=?");
        $stmt->bind_param("sis", $status, $id, $room_code);
        $stmt->execute();

    } elseif (isset($_POST['delete'])) {
        // Hapus lampu
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM lamps WHERE id=? AND room_code=?");
        $stmt->bind_param("is", $id, $room_code);
        $stmt->execute();

    } elseif (isset($_POST['join_room'])) {
        // Gabung ke room lain
        $new_room_code = $_POST['room_code'];
        $queryRoom = $conn->prepare("SELECT id FROM users WHERE room_code = ?");
        $queryRoom->bind_param("s", $new_room_code);
        $queryRoom->execute();
        $resultRoom = $queryRoom->get_result();

        if ($resultRoom->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE users SET room_code=? WHERE username=?");
            $stmt->bind_param("ss", $new_room_code, $user);
            $stmt->execute();
            header("Location: main.php");
            exit();
        } else {
            echo "<script>alert('Room Code tidak ditemukan!');</script>";
        }
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit();
        
    } elseif (isset($_POST['leave_room'])) {
        $default_room_code = $userData['id']; // Set room ke ID sendiri
        $stmt = $conn->prepare("UPDATE users SET room_code=? WHERE username=?");
        $stmt->bind_param("ss", $default_room_code, $user);
        $stmt->execute();
        
        header("Location: main.php");
        exit();
    }
    
}

// Ambil daftar lampu berdasarkan room_code
$queryLamps = $conn->prepare("SELECT * FROM lamps WHERE room_code = ?");
$queryLamps->bind_param("s", $room_code);
$queryLamps->execute();
$result = $queryLamps->get_result();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Lampu</title>
    <script>
        function openPopup() {
            document.getElementById("popup").style.display = "block";
        }
        function closePopup() {
            document.getElementById("popup").style.display = "none";
        }
        function showAccountDetails() {
            document.getElementById("account-popup").style.display = "block";
        }
        function hideAccountDetails() {
            document.getElementById("account-popup").style.display = "none";
        }
    </script>
    <style>
        body {
        font-family: Arial, sans-serif;
        background-color: #1a1a1a;
        color: #fff;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        overflow: hidden;
        font-family: 'Space Mono', monospace;
        }

        #popup, #account-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #333;
            padding: 20px;
            box-shadow: 0px 0px 10px gray;
            height: 50vh;
        }
        .account-button {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        box-shadow: 0px 0px 10px gray;
        }

        thead {
            background-color: #333;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #444;
            width: 40vh;
        }

        th {
            background-color: #444;
        }

        td button {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }

        td .edit-button {
            background-color: #3498db;
            color: #fff;
        }

        td .delete-button {
            background-color: #e74c3c;
            color: #fff;
        }

        .confirm-button, .register-button {
            width: 50%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
            margin-top: 1vh;
        }

        .tambah-button {
            width: 20%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        .close-button {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 20%;
            background: none;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
        }

        .input-field {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #444;
        background-color: #1a1a1a;
        color: #fff;
        border-radius: 5px;
        box-shadow: 0px 0px 10px gray;
    }
    </style>
</head>
<body>
    <button onclick="showAccountDetails()" class="account-button">Detail Akun</button>

    <div id="account-popup" class="popup">
        <h2>Detail Akun</h2>
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($userData['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
        <p><strong>Room Code:</strong> <?php echo htmlspecialchars($userData['room_code']); ?></p>
        <form method="post">
            <input type="text" name="room_code" placeholder="Masukkan Room Code" required>
            <button type="submit" name="join_room" class="confirm-button">Gabung ke Room</button>
        </form>
        <form method="post">
            <button type="submit" name="leave_room" class="tambah-button">Keluar dari Room</button>
        </form>
        <form method="post">
            <button type="submit" name="logout" class="tambah-button">Logout</button>
        </form>
        <button onclick="hideAccountDetails()" class="close-button">X</button>
    </div>

    <h2>Daftar Lampu</h2>
    <table border="1">
        <tr>
            <th>Nama Lampu</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="status" value="<?= $row['status'] ?>">
                    <button type="submit" name="toggle" class="confirm-button">Toggle</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete" class="confirm-button">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    
    <button onclick="openPopup()" class="tambah-button">Tambahkan Lampu</button>
    
    <div id="popup">
        <h2>Tambah Lampu</h2>
        <form method="post">
            <input type="text" name="name" value="Nama Lampu" required>
            <input type="text" name="serial_code" value="Serial Code" required>
            <button type="submit" name="add" class="confirm-button">Tambah</button>
            <button type="button" onclick="closePopup()" class="close-button">X</button>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
