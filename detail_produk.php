<?php
include 'db.php';
session_start();

if(!isset($_SESSION['username'])){
    header('Location: login.php');
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location: index.php');
    exit;
}

$id_produk = intval($_GET['id']);

$sql = "SELECT * FROM toko WHERE id_produk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "Produk tidak ditemukan.";
    exit;
}

$produk = $result->fetch_assoc();
$username = $_SESSION['username'];
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet/detail_produk.css">
    <title>Detail Produk</title>
</head>
<body>
    <div class="header">
        <h1>Detail Produk</h1>
    </div>

    <div class="detail-container"> 
        <div class="detail-image">
            <?php if(!empty($produk['gambar_produk'])): ?>
                <img src="<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
            
            <?php else: ?>
                <img src="uploads/placeholder.png" alt="Tidak ada gambar">
            
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <h2><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>
            <p><strong>Harga:</strong> Rp <?php echo number_format($produk['harga_produk'], 2, ',', '.'); ?></p>
            <p><strong>Deskripsi:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($produk['deskripsi_produk'])); ?></p>
        </div>
    </div>

    <div class="back-button">
        <a href="index.php">Kembali ke Produk</a>
    </div>
</body>
</html>
