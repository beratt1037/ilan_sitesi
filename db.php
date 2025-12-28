<?php
// db.php - Veritabanı ve Mail Ayarları (DÜZELTİLMİŞ VERSİYON)
date_default_timezone_set('Europe/Istanbul');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer dosyalarını dahil ediyoruz (Klasör yolların doğru kabul edilmiştir)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 1. Veritabanı Bağlantısı
// Resimdeki bilgilerini girdim:
$host = 'localhost';
$db   = 'berateuy_sahibinden'; 
$user = 'berateuy_php';        
$pass = 'berateuy_php_database'; 
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// 2. Mail Gönderme Fonksiyonu
function mailGonder($aliciEmail, $aliciAdi, $konu, $mesaj) {
    $mail = new PHPMailer(true);

    try {
        // --- SUNUCU AYARLARI ---
        $mail->isSMTP();                                            // SMTP kullan
        $mail->Host       = 'mail.berattoraman.com.tr';             // Sunucu adresi
        $mail->SMTPAuth   = true;                                   // SMTP doğrulaması açık
        $mail->Username   = 'sistem@berattoraman.com.tr';           // Mail adresi
        $mail->Password   = '@5259=12@Be';                          // Şifren (Resimden alındı)
        
        // --- DÜZELTME BURADA: STARTTLS için 587 Portu ---
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Şifreleme türü
        $mail->Port       = 587;                                    // 465 yerine 587 yaptık

        // --- GÖNDERİCİ ALICI AYARLARI ---
        $mail->setFrom('sistem@berattoraman.com.tr', 'berattoraman.com.tr');
        $mail->addAddress($aliciEmail, $aliciAdi);

        // --- İÇERİK AYARLARI ---
        $mail->isHTML(true);                                        // HTML formatında gönder
        $mail->CharSet = 'UTF-8';                                   // Türkçe karakter desteği
        $mail->Subject = $konu;
        $mail->Body    = $mesaj;
        $mail->AltBody = strip_tags($mesaj);                        // HTML desteklemeyenler için düz metin

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Hata olursa hatayı döndür
        return "Mail Gönderilemedi. Hata: {$mail->ErrorInfo}";
    }
}
?>