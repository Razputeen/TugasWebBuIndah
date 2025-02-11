<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lamp_control";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup'])) {
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Cek apakah email sudah terdaftar
        $check_email = $conn->query("SELECT email FROM users WHERE email='$email'");
        if ($check_email->num_rows > 0) {
            echo "<script>alert('Email sudah terdaftar, gunakan email lain!');</script>";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$nama', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Registrasi berhasil! Silakan login.');</script>";
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }
    }

    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user'] = $row['username'];
                header("Location: main.php");
                exit();
            } else {
                echo "<script>alert('Password salah.');</script>";
            }
        } else {
            echo "<script>alert('Email tidak ditemukan.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remote Lamp Control - Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="loading-screen" id="loading-screen">
        <img src="Asset/roof-house-logo.png" alt="Logo" class="logo">
        <div class="loader"></div>
    </div>

    <!-- Login Screen -->
    <div class="login-screen" id="login-screen" style="display: none;">
        <h1>Welcome back!</h1>
        <p>Always remember your account to light up</p>
        <form method="post">
            <input type="email" placeholder="Enter email" class="input-field" name="email" required>
            <input type="password" placeholder="Enter password" class="input-field" name="password" required>
            <button class="confirm-button" type="submit" name="login">Confirm</button>
            <span class="switch-link" onclick="showRegistration()">Don't have an account? Register here</span>
        </form>
    </div>

    <!-- Registration Screen -->
    <div class="registration-screen" id="registration-screen" style="display: none;">
        <h1>Create an Account</h1>
        <p>Register to control your lights</p>
        <form method="post">
            <input type="text" placeholder="Enter Name" class="input-field" name="nama" required>
            <input type="email" placeholder="Enter email" class="input-field" name="email" required>
            <input type="password" placeholder="Enter password" class="input-field" name="password" required>
            <button class="register-button" type="submit" name="signup">Register</button>
            <span class="switch-link" onclick="showLogin()">Already have an account? Login here</span>
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>
