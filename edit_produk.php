<?php
include 'db.php'; // Sertakan koneksi ke database
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_produk = intval($_GET['id']);

// Mengambil data produk dari tabel 'toko'
$sql = "SELECT id_produk, nama_produk, deskripsi_produk, harga_produk, gambar_produk FROM toko WHERE id_produk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Produk tidak ditemukan.";
    exit;
}

$produk = $result->fetch_assoc();

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
        // Validasi file gambar jika diupload
        if ($gambar['name']) {
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
                        // Hapus gambar lama jika ada
                        if (!empty($produk['gambar_produk']) && file_exists($produk['gambar_produk'])) {
                            unlink($produk['gambar_produk']);
                        }
                        $produk['gambar_produk'] = $upload_path;
                    } 
                    
                    else {
                        $error = "Gagal mengupload gambar.";
                    }
                }
            }
        }

        if (!isset($error)) {
            // Update data produk di tabel 'toko'
            $update_sql = "UPDATE toko SET nama_produk = ?, deskripsi_produk = ?, harga_produk = ?, gambar_produk = ? WHERE id_produk = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssdsi", $nama_produk, $deskripsi_produk, $harga, $produk['gambar_produk'], $id_produk);
            
            if ($stmt->execute()) {
                $success = "Produk berhasil diperbarui.";
                // Update variabel produk
                $produk['nama_produk'] = $nama_produk;
                $produk['deskripsi_produk'] = $deskripsi_produk;
                $produk['harga_produk'] = $harga;
            } 
            
            else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="stylesheet/edit_produk.css">
</head>
<body>
    <div class="header">
        <h1>Edit Produk</h1>
    </div>
    
    <div class="container">
        <div class="form-container">
            <?php if(isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="edit_produk.php?id=<?php echo htmlspecialchars($id_produk); ?>" method="post" enctype="multipart/form-data">
                <div class="input-container">
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                </div>
                
                <div class="input-container">
                    <label for="deskripsi_produk">Deskripsi Produk</label>
                    <textarea id="deskripsi_produk" name="deskripsi_produk" rows="5" required><?php echo htmlspecialchars($produk['deskripsi_produk']); ?></textarea>
                </div>

                <div class="input-container">
                    <label for="harga">Harga</label>
                    <input type="number" id="harga" name="harga" step="0.01" min="0" value="<?php echo htmlspecialchars($produk['harga_produk']); ?>" required>
                </div>
                
                <div class="input-container">
                    <label for="gambar_produk">Gambar Produk</label>
                    <input type="file" id="gambar_produk" name="gambar_produk" accept="image/*">
                </div>
    
                <?php if (!empty($produk['gambar_produk'])): ?>
                    <img src="<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                <?php endif; ?>

                <button type="submit">Simpan Perubahan</button>
            </form>
        </div>

        <div class="back-button">
            <a href="index.php">Kembali ke Produk</a>
        </div>
    </div>
</body>
</html>
