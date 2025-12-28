<?php
require 'db.php';
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Şehirleri çek (Dropdown için)
$cities = $pdo->query("SELECT * FROM cities ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
// Kategorileri çek
$categories = $pdo->query("SELECT * FROM categories ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];
    $city_id = $_POST['city_id'];
    $dist_id = $_POST['district_id'];
    $user_id = $_SESSION['user_id'];
    
    // Resim Yükleme İşlemi
    $image_name = "no-image.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_name)) {
            $image_name = $new_name;
        }
    }

    $sql = "INSERT INTO listings (category_id, user_id, title, price, city_id, district_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$cat_id, $user_id, $title, $price, $city_id, $dist_id, $image_name])) {
        $message = "<p style='color:green; font-weight:bold;'>İlan başarıyla yayınlandı! Yönlendiriliyorsunuz...</p>";
        header("refresh:2;url=index.php");
    } else {
        $message = "<p style='color:red;'>Bir hata oluştu.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ücretsiz İlan Ver</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth_style.css">
    <script>
        // Sayfa yüklenirken temayı anında uygula (Beyaz parlama olmasın)
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
<body>

<div class="auth-card" style="max-width: 600px; margin: 40px auto;">
    <h2>İlan Ver</h2>
    <?= $message ?>
    <form action="add-listing.php" method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label>İlan Başlığı</label>
            <input type="text" name="title" placeholder="Örn: Tertemiz kullanılmış bisiklet" required>
        </div>

        <div class="input-group">
            <label>Fiyat (TL)</label>
            <input type="number" name="price" placeholder="0" required>
        </div>

        <div class="input-group">
            <label>Kategori</label>
            <select name="category_id" required style="width:100%; padding:10px; border-radius:8px; background:var(--input-bg); color:var(--text-color);">
                <option value="">Kategori Seçiniz</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['title'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex; gap:15px;">
            <div class="input-group" style="flex:1;">
                <label>İl</label>
                <select name="city_id" id="citySelect" required style="width:100%; padding:10px; border-radius:8px; background:var(--input-bg); color:var(--text-color);">
                    <option value="">Seçiniz</option>
                    <?php foreach($cities as $city): ?>
                        <option value="<?= $city['id'] ?>"><?= $city['title'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group" style="flex:1;">
                <label>İlçe</label>
                <select name="district_id" id="districtSelect" required disabled style="width:100%; padding:10px; border-radius:8px; background:var(--input-bg); color:var(--text-color);">
                    <option value="">Önce İl Seçiniz</option>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label>İlan Resmi</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit" class="btn-submit">Yayınla</button>
        <a href="index.php" style="display:block; margin-top:20px; font-size:14px; text-align:center; color:var(--text-color); opacity:0.7; text-decoration:none;">Vazgeç</a>
    </form>
</div>

<script src="script.js"></script>
</body>
</html>