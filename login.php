<?php
require 'db.php';
session_start();

$error = "";

// Zaten giriş yapmışsa ana sayfaya gönder
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Şifre kontrolü
        if ($user && password_verify($password, $user['password'])) {
            // Başarılı giriş, session bilgilerini doldur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_surname'] = $user['last_name'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "E-posta adresi veya şifre hatalı!";
        }
    } else {
        $error = "Lütfen tüm alanları doldurun.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Berat Toraman</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="auth_style.css"> <script>
        // Sayfa açılırken karanlık modu kontrol et
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    </script>
</head>
<body>

<div class="auth-card">
    <div class="logo-area" style="margin-bottom: 20px;">
        <a href="index.php" style="color: var(--primary-color); font-size: 24px; font-weight: bold; text-decoration: none;">
           BeratToraman
        </a>
    </div>
    <h2>Giriş Yap</h2>
    
    <?php if($error): ?>
        <p style="color: #ff4d4d; font-size: 14px; margin-bottom: 15px;"><?= $error ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="input-group">
            <label>E-posta Adresi</label>
            <input type="email" name="email" placeholder="mail@ornek.com" required>
        </div>
        
        <div class="input-group">
            <label>Şifre</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">Devam Et</button>
        <div style="text-align: right; margin-bottom: 15px;">
    <a href="sifremi-unuttum.php" style="color: #003399; text-decoration: none; font-size: 14px;">
        Şifremi Unuttum?
    </a>
</div>
    </form>
    
    <div class="auth-footer">
        Henüz üye değil misin? <a href="register.php">Hemen Kayıt Ol</a>
    </div>
</div>

</body>
</html>