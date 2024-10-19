<?php
include 'db.php'; // Sertakan koneksi ke database
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi_produk = trim($_POST['deskripsi_produk']);
    $harga = floatval($_POST['harga']);
    $gambar = $_FILES['gambar_produk'];

    // Validasi input
    if (empty($nama_produk) || empty($deskripsi_produk) || $harga <= 0) {
        $error = "Silakan lengkapi semua bidang dengan benar.";
    } 
    
    else {
        // Validasi file gambar
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($gambar['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $error = "Jenis file gambar tidak diizinkan.";
        } 
        
        elseif ($gambar['size'] > 2 * 1024 * 1024) { // 2MB
            $error = "Ukuran file gambar terlalu besar. Maksimum 2MB.";
        } 
        
        else {
            // Cek MIME type
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $gambar['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_mime_types)) {
                $error = "File yang diupload bukan gambar yang valid.";
            } 
            
            else {
                // Buat nama file unik
                $unique_name = uniqid() . '.' . $file_extension;
                $upload_dir = 'uploads/produk/';
                $upload_path = $upload_dir . $unique_name;

                // Pastikan direktori upload ada
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Pindahkan file ke direktori upload
                if (move_uploaded_file($gambar['tmp_name'], $upload_path)) {
                    // Simpan data ke database
                    $sql = "INSERT INTO toko (nama_produk, deskripsi_produk, harga_produk, gambar_produk) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssds", $nama_produk, $deskripsi_produk, $harga, $upload_path);
                    
                    if ($stmt->execute()) {
                        $success = "Produk berhasil ditambahkan.";
                        // Reset form
                        $nama_produk = $deskripsi_produk = '';
                        $harga = 0;
                    } 
                    
                    else {
                        $error = "Error: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } 
                
                else {
                    $error = "Gagal mengupload gambar.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="stylesheet/tambah_produk.css"> 
</head>
<body>
    <div class="header">
        <h1>Tambah Produk</h1>
    </div>

    <div class="container">
        <div class="form-container">
            <?php if(isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="tambah_produk.php" method="post" enctype="multipart/form-data">
                <div class="input-container">
                    <input type="text" id="nama_produk" name="nama_produk" value="<?php echo isset($nama_produk) ? htmlspecialchars($nama_produk) : ''; ?>" required placeholder=" ">
                    <label for="nama_produk">Nama Produk</label>
                </div>

                <div class="input-container">
                    <textarea id="deskripsi_produk" name="deskripsi_produk" rows="5" required placeholder=" "><?php echo isset($deskripsi_produk) ? htmlspecialchars($deskripsi_produk) : ''; ?></textarea>
                    <label for="deskripsi_produk">Deskripsi Produk</label>
                </div>

                <div class="input-container">
                    <input type="number" id="harga" name="harga" step="0.01" min="0" value="<?php echo isset($harga) ? htmlspecialchars($harga) : ''; ?>" required placeholder=" ">
                    <label for="harga">Harga</label>
                </div>

                <div class="input-container">
                    <input type="file" id="gambar_produk" name="gambar_produk" accept="image/*" required placeholder=" ">
                    <label for="gambar_produk">Gambar Produk</label>
                </div>

                <button type="submit">Tambah Produk</button>
            </form>
        </div>

        <div class="back-button">
            <a href="index.php">Kembali ke Produk</a>
        </div>
    </div>
</body>
</html>
