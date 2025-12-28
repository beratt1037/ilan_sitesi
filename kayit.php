<?php
// kayit.php - Tam ve Güncel Hali
require 'db.php';

$mesaj = ""; // Ekrana basılacak mesajlar için değişken
$mesajTuru = ""; // success veya error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Formdan Gelen Verileri Al ve Temizle
    $ad    = trim($_POST['first_name']); 
    $soyad = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    // Şifreyi güvenli hale getir (Hashle)
    $sifre = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    // 6 Haneli Rastgele Doğrulama Kodu Üret
    $dogrulamaKodu = rand(100000, 999999);

    try {
        // 2. E-posta Daha Önce Alınmış mı Kontrol Et
        $kontrol = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $kontrol->execute([$email]);
        
        if ($kontrol->rowCount() > 0) {
            // E-posta zaten varsa uyarı ver (Veritabanı hatası verdirtme)
            $mesaj = "Bu e-posta adresi zaten sistemde kayıtlı! <br> <a href='login.php'>Giriş yapmak için tıklayın.</a>";
            $mesajTuru = "error";
        } else {
            // 3. E-posta yoksa Kayıt İşlemini Yap
            $sql = "INSERT INTO users (first_name, last_name, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$ad, $soyad, $email, $sifre, $dogrulamaKodu])) {
                
                // --- MAİL GÖNDERME KISMI ---
                $mailIcerik = "
                <h3>Merhaba $ad $soyad,</h3>
                <p>Aramıza hoş geldin! Kaydını tamamlamak için doğrulama kodun aşağıdadır:</p>
                <h1 style='background-color:#eee; padding:10px; display:inline-block; letter-spacing:5px;'>$dogrulamaKodu</h1>
                <p>Bu kodu doğrulama ekranına girerek hesabını aktif edebilirsin.</p>
                ";

                // db.php içindeki fonksiyonu çağır
                $mailSonuc = mailGonder($email, "$ad $soyad", "Hesap Doğrulama Kodu", $mailIcerik);
                
                if ($mailSonuc === true) {
                    // BAŞARILI: Kullanıcıyı dogrula.php sayfasına yönlendir
                    // Email'i de URL'de gönderiyoruz ki kullanıcı tekrar yazmasın
                    header("Location: dogrula.php?email=" . urlencode($email));
                    exit();
                } else {
                    // Veritabanına yazıldı ama mail gitmediyse
                    $mesaj = "Kayıt yapıldı ancak mail gönderilemedi. Hata: " . $mailSonuc;
                    $mesajTuru = "error";
                }
            }
        }
    } catch (PDOException $e) {
        $mesaj = "Veritabanı Hatası: " . $e->getMessage();
        $mesajTuru = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; background: #ffe6e6; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Kayıt Ol</h2>

    <?php if ($mesaj != ""): ?>
        <div class="<?php echo $mesajTuru; ?>">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Adınız:</label>
        <input type="text" name="first_name" required>

        <label>Soyadınız:</label>
        <input type="text" name="last_name" required>

        <label>E-posta Adresiniz:</label>
        <input type="email" name="email" required>

        <label>Şifreniz:</label>
        <input type="password" name="password" required>

        <button type="submit">Kayıt Ol</button>
    </form>
    
    <p style="text-align:center;">
        Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
    </p>
</div>

</body>
</html>