<?php
require 'db.php'; 

$mesaj = "";
$mesajTuru = "";
$formGoster = false;

// URL'den gelen verileri al
$email = isset($_GET['email']) ? $_GET['email'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$email || !$token) {
    $mesaj = "Hatalı veya eksik link! Lütfen maildeki linke tekrar tıklayın.";
    $mesajTuru = "error";
} else {
    // 1. Veritabanından kullanıcıyı ve süreyi kontrol et
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE email = ? AND reset_token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Süre Kontrolü (Veritabanındaki tarih > Şu an)
        if (strtotime($user['reset_expires']) > time()) {
            
            $formGoster = true; // Süre ve token geçerli, formu göster!

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $pass1 = $_POST['pass1'];
                $pass2 = $_POST['pass2'];

                if ($pass1 === $pass2) {
                    // Şifreler eşleşiyor, güncelleme yap
                    $hashedPassword = password_hash($pass1, PASSWORD_DEFAULT);

                    // Şifreyi güncelle, token ve süreyi sıfırla (Link tekrar kullanılamasın)
                    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                    
                    if ($update->execute([$hashedPassword, $user['id']])) {
                        $mesaj = "Şifreniz başarıyla değiştirildi! Giriş sayfasına yönlendiriliyorsunuz...";
                        $mesajTuru = "success";
                        $formGoster = false; // Formu gizle
                        header("refresh:3;url=login.php");
                    } else {
                        $mesaj = "Veritabanı hatası oluştu.";
                        $mesajTuru = "error";
                    }
                } else {
                    $mesaj = "Girdiğiniz şifreler birbiriyle uyuşmuyor.";
                    $mesajTuru = "error";
                }
            }

        } else {
            $mesaj = "Bu sıfırlama linkinin süresi dolmuş.";
            $mesajTuru = "error";
        }
    } else {
        $mesaj = "Geçersiz link! Token hatalı veya daha önce kullanılmış.";
        $mesajTuru = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Şifre Belirle</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #eef2f5; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h3 { margin-top: 0; color: #333; text-align: center; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #666; font-size: 14px; font-weight: 600; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; transition: 0.3s; }
        input:focus { border-color: #0044cc; outline: none; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        button:hover { background: #218838; }
        .success { color: #155724; background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
        .error { color: #721c24; background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #0044cc; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="box">
    <h3>🔐 Yeni Şifre Oluştur</h3>

    <?php if ($mesaj): ?>
        <div class="<?= $mesajTuru ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <?php if ($formGoster): ?>
        <form method="POST">
            <label>Yeni Şifre</label>
            <input type="password" name="pass1" required placeholder="Yeni şifrenizi girin" minlength="6">
            
            <label>Yeni Şifre (Tekrar)</label>
            <input type="password" name="pass2" required placeholder="Şifrenizi tekrar girin" minlength="6">
            
            <button type="submit">Şifreyi Güncelle</button>
        </form>
    <?php else: ?>
        <a href="sifremi-unuttum.php" class="back-link">Yeni Link İste</a>
        <a href="login.php" class="back-link">Giriş Yap</a>
    <?php endif; ?>

</div>

</body>
</html>