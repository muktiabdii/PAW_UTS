<?php
    session_start();
    include 'db.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Mengambil dan membersihkan input
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Validasi input
        if (empty($username) || empty($password)) {
            $error = "Semua field wajib diisi.";
        } 
        else {
            // Menggunakan prepared statements
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Mencari data username pada database
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Mengecek password yang sudah dimasukkan dan password yang ada pada database
                if (password_verify($password, $row['password'])) {
                    // Menyimpan data sesi
                    $_SESSION['username'] = $username;
                    header('Location: index.php');
                    exit;
                } 

                else {
                    $error = "Password tidak valid.";
                }
            } 

            else {
                $error = "Pengguna tidak ditemukan.";
            }
            
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign in</title>
    <link rel="stylesheet" href="stylesheet/login.css">
</head>
<body>
    <div class="container">
        <h2>Sign in</h2>

        <form method="post" action="login.php">
            <div class="input-container">
                <input type="text" name="username" id="username" placeholder=" " required>
                <label for="username">Username</label>
            </div>

            <div class="input-container">
                <input type="password" name="password" id="password" placeholder="" required>
                <label for="password">Password</label>
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Tidak memiliki akun? <a href="register.php">Register</a></p>
    
        <?php if(isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
