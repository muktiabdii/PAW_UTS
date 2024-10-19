<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil dan membersihkan input
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi.";
    } 
    
    elseif (strlen($username) < 5) { // Validasi panjang username
        $error = "Username harus terdiri dari minimal 5 karakter.";
    } 
    
    elseif ($password !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok.";
    } 
    
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Cek apakah username sudah ada
        if ($stmt->num_rows > 0) {
            $error = "Username sudah digunakan.";
        } 
        
        else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert pengguna baru
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['username'] = $username; 
                header('Location: login.php');
                exit;
            } 
            
            else {
                $error = "Terjadi kesalahan saat registrasi: " . $conn->error;
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Akun</title> <!-- Mengubah judul -->
    <link rel="stylesheet" href="stylesheet/register.css">
</head>
<body>
    <div class="container">
        <h2>Registrasi</h2> <!-- Mengubah judul di halaman -->
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="register.php">
            <div class="input-container">
                <input type="text" id="username" name="username" required placeholder=" ">
                <label for="username" class="floating-label">Username</label>
            </div>

            <div class="input-container">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password" class="floating-label">Password</label>
            </div>

            <div class="input-container">
                <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                <label for="confirm_password" class="floating-label">Konfirmasi Password</label>
            </div>


            <button type="submit">Daftar</button> <!-- Mengubah teks tombol -->
        </form>
        
        <p>Sudah memiliki akun? <a href="login.php">Masuk</a></p> <!-- Mengubah teks -->
    </div>
</body>
</html>
