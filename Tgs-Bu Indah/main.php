<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lamp_control";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['user'];
$resultUser = $conn->query("SELECT * FROM users WHERE username='$user'");
$userData = $resultUser->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $serial_code = $_POST['serial_code'];
        $conn->query("INSERT INTO lamps (name, serial_code) VALUES ('$name', '$serial_code')");
    } elseif (isset($_POST['toggle'])) {
        $id = $_POST['id'];
        $status = $_POST['status'] == 'ON' ? 'OFF' : 'ON';
        $conn->query("UPDATE lamps SET status='$status' WHERE id=$id");
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM lamps WHERE id=$id");
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

$result = $conn->query("SELECT * FROM lamps");
?>

<!DOCTYPE html>
<html>
<head>
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

        body {
        font-family: Arial, sans-serif;
        background-color: #1a1a1a;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        overflow: hidden;
        font-family: 'Space Mono', monospace;
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

    <div id="account-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <h2>Detail Akun</h2>
            <p><strong>Nama:</strong> <?php echo $userData['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
            <form method="post">
                <button type="submit" name="logout" class="tambah-button">Logout</button>
            </form>
            <button onclick="hideAccountDetails()" class="close-button">X</button>
        </div>
    </div>

    <div class="container">
    <h2>Daftar Lampu</h2>
    <table border="1">
        <tr>
            <th>Nama Lampu</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['status'] ?></td>
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
            <input type="text" name="name" required class="input-field">
            <input type="text" name="serial_code" required class="input-field">
            <button type="submit" name="add" class="confirm-button">Tambah</button>
            <button type="button" onclick="closePopup()" class="close-button">X</button>
        </form>
    </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
