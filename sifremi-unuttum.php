<?php
require 'db.php'; // db.php içinde timezone ayarı yaptık

$mesaj = "";
$mesajTuru = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // 1. Kullanıcı var mı?
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Token Oluştur
        $token = bin2hex(random_bytes(32));
        
        // SÜRE AYARI: Şu anki Türkiye saati + 1 saat
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 3. Veritabanına Yaz
        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        
        if ($update->execute([$token, $expires, $email])) {
            
            // LİNK OLUŞTURMA
            $domain = $_SERVER['SERVER_NAME'];
            $safeEmail = urlencode($email);
            $link = "http://$domain/sifre-sifirla.php?email=$safeEmail&token=$token";

            // --- PROFESYONEL MAİL TASARIMI ---
            
            // Site Ana Rengin (Mavi: #0044cc, Kırmızı: #cc0000 vs.)
            $renk = "#0044cc"; 
            
            $icerik = "
            <div style='background-color: #f6f6f6; font-family: sans-serif; padding: 40px 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-top: 5px solid $renk;'>
                    
                    <h2 style='color: #333; text-align: center; margin-bottom: 20px;'>Şifre Sıfırlama Talebi</h2>
                    
                    <p style='font-size: 16px; color: #555; line-height: 1.5;'>
                        Merhaba <strong>{$user['first_name']}</strong>,
                    </p>
                    
                    <p style='font-size: 16px; color: #555; line-height: 1.5;'>
                        Hesabınız için şifre sıfırlama talebinde bulundunuz. Yeni şifrenizi belirlemek için aşağıdaki butona tıklayabilirsiniz.
                    </p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$link' style='background-color: $renk; color: white; padding: 14px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;'>
                            Şifremi Yenile
                        </a>
                    </div>

                    <p style='font-size: 14px; color: #999; text-align: center;'>
                        Eğer butona tıklayamıyorsanız, şu linki tarayıcınıza yapıştırın:<br>
                        <a href='$link' style='color: $renk;'>$link</a>
                    </p>
                    
                    <div style='border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px; text-align: center; font-size: 12px; color: #aaa;'>
                        Bu işlem talebiniz üzerine gönderilmiştir. Eğer siz yapmadıysanız bu maili görmezden gelin.<br>
                        &copy; " . date("Y") . " " . $_SERVER['SERVER_NAME'] . "
                    </div>
                </div>
            </div>
            ";
            // --- TASARIM BİTİŞ ---

            $mailSonuc = mailGonder($email, $user['first_name'], "Sifre Sifirlama Talebi", $icerik);

            if ($mailSonuc === true) {
                $mesaj = "Sıfırlama linki şık bir mail ile gönderildi! Lütfen kontrol et.";
                $mesajTuru = "success";
            } else {
                $mesaj = "Mail gönderim hatası: " . $mailSonuc;
                $mesajTuru = "error";
            }
        }
    } else {
        $mesaj = "Bu mail adresi sistemde kayıtlı değil.";
        $mesajTuru = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #eef2f5; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h3 { margin-top: 0; color: #333; }
        p { color: #666; font-size: 14px; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; transition: 0.3s; }
        input:focus { border-color: #0044cc; outline: none; }
        button { width: 100%; padding: 12px; background: #0044cc; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        button:hover { background: #003399; }
        .success { color: #155724; background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .error { color: #721c24; background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        a { color: #0044cc; text-decoration: none; font-size: 14px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h3>🔐 Şifremi Unuttum</h3>
        <p>E-posta adresinizi girin, size sıfırlama bağlantısı gönderelim.</p>
        
        <?php if($mesaj): ?><div class="<?=$mesajTuru?>"><?=$mesaj?></div><?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" required placeholder="ornek@mail.com">
            <button type="submit">Link Gönder</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="login.php">Giriş Yap</a>
        </div>
    </div>
</body>
</html>