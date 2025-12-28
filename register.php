<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol - Berat Toraman</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Kayıt Formuna Özel Basit Stil */
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Taşmaları engeller */
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #003399; /* Senin tema rengin */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-submit:hover {
            background-color: #002266;
        }
    </style>
</head>
<body style="background-color: #f4f4f4;">

    <header class="main-header">
        <div class="container header-wrapper">
            <div class="logo"><a href="index.php">BeratToraman</a></div>
        </div>
    </header>

    <div class="container">
        <div class="register-container">
            <h2 style="text-align:center; margin-bottom: 20px;">Aramıza Katıl</h2>
            
            <form action="kayit.php" method="POST">
                
                <div class="form-group">
                    <label>Adınız</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Örn: Ahmet" required>
                </div>

                <div class="form-group">
                    <label>Soyadınız</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Örn: Yılmaz" required>
                </div>

                <div class="form-group">
                    <label>E-posta Adresi</label>
                    <input type="email" name="email" class="form-control" placeholder="ornek@mail.com" required>
                </div>

                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" class="form-control" placeholder="******" required>
                </div>

                <button type="submit" class="btn-submit">Kayıt Ol</button>

            </form>
            
            <p style="text-align:center; margin-top:15px; font-size:14px;">
                Zaten üye misin? <a href="login.php" style="color:#003399;">Giriş Yap</a>
            </p>
        </div>
    </div>

</body>
</html>