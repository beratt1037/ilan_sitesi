# 🔐 PHP Güvenli Kullanıcı Yönetim Sistemi

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

> **Modern arayüze sahip, güvenli, PDO altyapılı ve e-posta doğrulamalı kullanıcı kayıt/giriş sistemi.**

Bu proje, web uygulamaları için **güvenli bir kimlik doğrulama (Authentication)** altyapısı sunar. Kullanıcıların kayıt olması, e-posta ile hesaplarını doğrulaması, güvenli giriş yapması ve şifrelerini sıfırlaması işlemlerini kapsar.

---

## 🚀 Özellikler

- **🔒 Güvenli Altyapı:** SQL Injection'a karşı PDO kullanımı ve `password_hash` ile şifreleme.
- **📩 E-posta Doğrulama:** Kayıt sonrası SMTP üzerinden 6 haneli doğrulama kodu gönderimi.
- **🔑 Şifre Sıfırlama:** Token tabanlı, süreli (1 saat geçerli) ve güvenli şifre yenileme sistemi.
- **🎨 Modern UI/UX:** Sarı ve Gri tonlarında, tamamen mobil uyumlu (Responsive) ve kullanıcı dostu arayüz.
- **📱 Responsive:** Telefon, tablet ve masaüstü cihazlarla tam uyumlu.

---

## 🛠️ Kullanılan Teknolojiler

| Teknoloji | Açıklama |
|-----------|----------|
| **PHP 8.x** | Backend işlemleri ve mantıksal kurgu. |
| **MySQL** | Kullanıcı verilerinin güvenli depolanması. |
| **PDO** | Güvenli veritabanı bağlantısı ve sorgu yönetimi. |
| **HTML5 & CSS3** | Modern Flexbox yapısı ile özelleştirilmiş tasarım. |
| **SMTP** | E-posta gönderim servisi. |

---

## 📸 Ekran Görüntüleri

Projenin arayüzünden örnek görünümler:

| Giriş Yap (Login) | Kayıt Ol (Register) |
|-------------------|---------------------|
| <img src="img/login-screen.png" width="400"> | <img src="img/register-screen.png" width="400"> |

| Şifremi Unuttum | E-posta Tasarımı |
|-----------------|------------------|
| <img src="img/forgot-password.png" width="400"> | <img src="img/email-template.png" width="400"> |

*(Not: Ekran görüntülerini projenize `img` klasörü açıp içine ekleyerek buradaki isimlerle eşleştiriniz.)*

---

## ⚙️ Kurulum

Projeyi yerel sunucunuzda (localhost) çalıştırmak için adımları izleyin:

### 1. Veritabanını Oluşturun
PhpMyAdmin veya MySQL arayüzünüzde yeni bir veritabanı oluşturun ve aşağıdaki SQL kodunu çalıştırın:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verification_code INT DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
