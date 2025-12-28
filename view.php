<?php
session_start();
require 'db.php';

$id = $_GET['id'] ?? 0;

// İlanı, kategorisini, şehrini, ilçesini ve satıcı adını tek seferde çekiyoruz
$sql = "SELECT l.*, c.title as cat_name, city.title as city_name, d.title as district_name, u.first_name, u.last_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id
        JOIN cities city ON l.city_id = city.id
        JOIN districts d ON l.district_id = d.id
        JOIN users u ON l.user_id = u.id
        WHERE l.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$ad = $stmt->fetch();

if (!$ad) { die("İlan bulunamadı!"); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($ad['title']) ?> | BeratToraman</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-container { width: 1180px; margin: 20px auto; display: flex; gap: 30px; background: var(--container-bg); padding: 20px; border-radius: 8px; }
        .ad-gallery { flex: 2; }
        .ad-gallery img { width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .ad-sidebar { flex: 1; padding: 20px; border: 1px solid var(--border-color); border-radius: 8px; }
        .price-tag { font-size: 28px; color: #003399; font-weight: bold; margin-bottom: 20px; }
        .seller-info { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px; border-left: 5px solid #FFD200; }
        [data-theme="dark"] .seller-info { background: #222; color: #eee; }
    </style>
</head>
<body>
    <div class="details-container">
        <div class="ad-gallery">
            <img src="uploads/<?= $ad['image_url'] ?>" onerror="this.src='uploads/no-image.jpg'">
            <h1><?= htmlspecialchars($ad['title']) ?></h1>
            <p><?= $ad['city_name'] ?> / <?= $ad['district_name'] ?> / <?= $ad['cat_name'] ?></p>
        </div>

        <div class="ad-sidebar">
            <div class="price-tag"><?= number_format($ad['price'], 0, ',', '.') ?> TL</div>
            <hr>
            <div class="seller-info">
                <strong>İlan Sahibi:</strong><br>
                <?= htmlspecialchars($ad['first_name'] . " " . $ad['last_name']) ?>
            </div>
            <button class="btn-post" style="width:100%; margin-top:20px; text-align:center;">Mesaj Gönder</button>
        </div>
    </div>
</body>
</html>