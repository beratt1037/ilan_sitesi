<?php
require 'db.php';

$mesaj = "";
$durum = ""; // success veya error için

// URL'den gelen e-postayı al (Kullanıcı tekrar yazmasın diye)
$gelenEmail = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $girilenKod = $_POST['kod'];

    // 1. Veritabanında bu mail ve bu kod eşleşiyor mu bak
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
    $stmt->execute([$email, $girilenKod]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Kod DOĞRU ise: Hesabı onayla (is_verified = 1 yap)
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        
        $durum = "success";
        $mesaj = "Tebrikler! Hesabınız doğrulandı. Giriş yapabilirsiniz.";
        // İstersen 2 saniye sonra giriş sayfasına atabilirsin:
        header("refresh:2;url=login.php");
    } else {
        // 3. Kod YANLIŞ ise
        $durum = "error";
        $mesaj = "Hatalı kod girdiniz! Lütfen mailinizi kontrol edip tekrar deneyin.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesap Doğrulama</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .kutu { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 350px; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 20px; text-align: center; letter-spacing: 5px; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        .mesaj { margin-bottom: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="kutu">
        <h2>Hesap Doğrulama</h2>
        <p>Mail adresinize (<?php echo htmlspecialchars($gelenEmail); ?>) gelen 6 haneli kodu giriniz.</p>

        <?php if($mesaj): ?>
            <div class="mesaj <?php echo $durum; ?>">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <?php if($durum != "success"): ?>
        <form method="POST" action="">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($gelenEmail); ?>">
            
            <input type="text" name="kod" placeholder="123456" maxlength="6" required>
            <button type="submit">Doğrula</button>
        </form>
        <?php else: ?>
            <p>Giriş sayfasına yönlendiriliyorsunuz...</p>
            <a href="login.php">Beklemek istemiyorsanız tıklayın</a>
        <?php endif; ?>
    </div>

</body>
</html>