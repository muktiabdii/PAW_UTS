<?php
include 'db.php'; // Sertakan koneksi ke database
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Query untuk mengambil data produk dari database
$sql = "SELECT id_produk, nama_produk, gambar_produk, harga_produk FROM toko";
$result = $conn->query($sql);
$username = $_SESSION['username'];

// Hapus produk
if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);
    $delete_sql = "DELETE FROM toko WHERE id_produk = $id_produk"; // Mengubah 'produk' menjadi 'toko'
    $conn->query($delete_sql);
    header('Location: index.php'); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsivitas -->
    <title>Produk</title>
    <link rel="stylesheet" href="stylesheet/produk.css">
</head>
<body>
    <header>
        <h1>Fashops</h1>
    </header>

    <div class="tambah-produk">
        <button onclick="location.href='tambah_produk.php'">Tambah Produk</button>
    </div>

    <div class="produk-container">
        <?php
        // Menampilkan data produk dalam grid
        while($row = $result->fetch_assoc()): ?>
            <div class="produk-item">
                <a href="detail_produk.php?id=<?php echo htmlspecialchars($row['id_produk']); ?>"> <!-- Link ke detail produk -->

                    <?php if(!empty($row['gambar_produk'])): ?>
                        <img src="<?php echo htmlspecialchars($row['gambar_produk']); ?>" alt="<?php echo htmlspecialchars(($row['nama_produk'])); ?>">
                    
                    <?php else: ?>
                        <img src="uploads/placeholder.png" alt="Tidak ada gambar">
                    
                    <?php endif; ?>
                </a>

                <p><?php echo htmlspecialchars($row['nama_produk']); ?></p>
                <p>Harga: Rp <?php echo number_format($row['harga_produk'], 0, ',', '.'); ?></p>
                
                <div class="aksi-produk">
                    <button onclick="location.href='edit_produk.php?id=<?php echo htmlspecialchars($row['id_produk']); ?>'">Edit</button>
                    <button onclick="if(confirm('Apakah anda yakin ingin menghapus produk ini?')) location.href='index.php?hapus=<?php echo htmlspecialchars($row['id_produk']); ?>'">Hapus</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="logout-container">
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>

    <footer>
        <p>Instagram : @Fashops</p>
        <p>Tiktok    : @Fashops</p>
    </footer>
</body>
</html>
