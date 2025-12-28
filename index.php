<?php
// 1. ADIM: Oturumu başlat
session_start();
require 'db.php';

// --- VERİ ÇEKME VE FİLTRELEME ---

// Şehir Listesini Çek
$cities = $pdo->query("SELECT * FROM cities ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Filtreleri Al
$catFilter = isset($_GET['cat']) ? $_GET['cat'] : null;
$cityFilter = isset($_GET['city_id']) ? $_GET['city_id'] : null;
$districtFilter = isset($_GET['district_id']) ? $_GET['district_id'] : null;

// --- KATEGORİ MANTIĞI (GÜNCELLENDİ) ---
// Tüm kategorileri çekip Baba-Oğul ilişkisine göre hazırlıyoruz
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY title ASC");
$allCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$categoriesTree = []; // Ana kategoriler
$subCategories = [];  // Alt kategoriler

foreach ($allCategories as $cat) {
    if ($cat['parent_id'] == null) {
        $categoriesTree[] = $cat;
    } else {
        $subCategories[$cat['parent_id']][] = $cat;
    }
}

// İlan Sorgusunu Hazırla (JOIN işlemleri ile)
$sql = "SELECT l.*, c.title as cat_name, city.title as city_name, d.title as district_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id
        JOIN cities city ON l.city_id = city.id 
        JOIN districts d ON l.district_id = d.id 
        WHERE 1=1";

$params = [];

// Hem ana kategori hem alt kategori seçimi için mantık
if ($catFilter) {
    // Seçilen kategori bir ana kategori mi yoksa alt kategori mi?
    // Basit olması için doğrudan category_id eşleşmesi yapıyoruz.
    // (Gelişmiş senaryoda: Ana kategori seçilirse altlarını da getirmeli, ama şimdilik ID bazlı bırakıyoruz)
    $sql .= " AND l.category_id = ?";
    $params[] = $catFilter;
}

if ($cityFilter) {
    $sql .= " AND l.city_id = ?";
    $params[] = $cityFilter;
}

if ($districtFilter) {
    $sql .= " AND l.district_id = ?";
    $params[] = $districtFilter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Berat Toraman</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Sayfa yüklenince temayı uygula
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    </script>
</head>
<body>

<header class="main-header">
    <div class="container header-wrapper">
        <div class="logo"><a href="index.php">BeratToraman</a></div>

        <div class="search-bar">
            <input type="text" placeholder="Kelime, ilan no ile ara">
            <button>Ara</button>
        </div>

        <div class="user-nav">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-info-box" style="display: flex; align-items: center; gap: 15px;">
                    <span class="user-display-name">
                        <i class="fa-solid fa-user"></i> 
                        <?php 
                           $name = $_SESSION['user_name'];
                           echo htmlspecialchars($name); 
                        ?>
                    </span>
                    <a href="logout.php" class="logout-link">Çıkış</a>
                </div>
            <?php else: ?>
                <div class="guest-nav">
                    <a href="login.php">Giriş Yap</a>
                    <a href="register.php">Kayıt Ol</a>
                </div>
            <?php endif; ?>
            <a href="add-listing.php" class="btn-post">Ücretsiz İlan Ver</a>
        </div>
    </div>
</header>

<div class="container main-content">
    
    <aside class="sidebar" style="width: 250px;">
        
        <div class="filter-section">
            <h4>Adres</h4>
            <form method="GET" action="">
                <?php if($catFilter): ?>
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($catFilter) ?>">
                <?php endif; ?>

                <select name="city_id" id="citySelect" style="width: 100%; margin-bottom: 10px; padding: 8px;">
                    <option value="">İl Seçiniz</option>
                    <?php foreach($cities as $city): ?>
                        <option value="<?= $city['id'] ?>" <?= $cityFilter == $city['id'] ? 'selected' : '' ?>>
                            <?= $city['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="district_id" id="districtSelect" style="width: 100%; margin-bottom: 10px; padding: 8px;" <?= !$cityFilter ? 'disabled' : '' ?>>
                    <option value="">İlçe Seçiniz</option>
                    </select>

                <button type="submit" style="width: 100%; background: #003399; color: white; border: none; padding: 10px; cursor: pointer;">Filtrele</button>
            </form>
        </div>

        <div class="filter-section sidebar-nav-box">
            <h4>Kategoriler</h4>
            <ul class="cat-list">
                <?php foreach($categoriesTree as $mainCat): ?>
                    <?php 
    // Alt kategori var mı?
    $hasSub = isset($subCategories[$mainCat['id']]); 
    
    // --- GÜNCELLENMİŞ İKON MANTIĞI (TÜM KATEGORİLER İÇİN) ---
    $iconClass = 'fa-folder'; // Varsayılan ikon
    
    $title = $mainCat['title']; // Kategori başlığını değişkene alalım, kod daha temiz olsun

    if (stripos($title, 'Emlak') !== false) {
        $iconClass = 'fa-house';
    } elseif (stripos($title, 'Vasıta') !== false) {
        $iconClass = 'fa-car';
    } elseif (stripos($title, 'Yedek Parça') !== false) {
        $iconClass = 'fa-screwdriver-wrench'; // Tamir seti
    } elseif (stripos($title, 'İkinci El') !== false) {
        $iconClass = 'fa-cart-shopping'; // Alışveriş sepeti
    } elseif (stripos($title, 'İş Makineleri') !== false) {
        $iconClass = 'fa-tractor'; // Traktör/İş Makinesi
    } elseif (stripos($title, 'Ustalar') !== false) {
        $iconClass = 'fa-paint-roller'; // Boya rulosu/Usta
    } elseif (stripos($title, 'Özel Ders') !== false) {
        $iconClass = 'fa-graduation-cap'; // Mezuniyet kepi
    } elseif (stripos($title, 'İş İlanları') !== false) {
        $iconClass = 'fa-briefcase'; // Evrak Çantası/İş
    } elseif (stripos($title, 'Hayvanlar') !== false) {
        $iconClass = 'fa-paw'; // Pati izi
    } elseif (stripos($title, 'Yardımcı') !== false) {
        $iconClass = 'fa-hands-holding-child'; // Yardım/Bakıcı
    }
?>
                    
                    <li class="cat-item">
                        <div class="cat-header">
                            <a href="?cat=<?= $mainCat['id'] ?>" class="cat-link-wrapper">
                                <span class="cat-icon-box">
                                    <i class="fa-solid <?= $iconClass ?>"></i>
                                </span>
                                <span class="cat-name"><?= htmlspecialchars($mainCat['title']) ?></span>
                            </a>

                            <?php if($hasSub): ?>
                                <button class="toggle-btn" type="button" onclick="toggleSubMenu(this)">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if($hasSub): ?>
                            <ul class="sub-menu">
                                <?php foreach($subCategories[$mainCat['id']] as $subCat): ?>
                                    <li>
                                        <a href="?cat=<?= $subCat['id'] ?>">
                                            <?= htmlspecialchars($subCat['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </aside>

    <main class="listings-area">
        <h2>İlanlar (<?= count($listings) ?>)</h2>
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">

        <div class="listing-grid">
            <?php if(count($listings) > 0): ?>
                <?php foreach($listings as $ad): ?>
                    <div class="ad-card">
                        <div class="ad-img">
                            <img src="uploads/<?= $ad['image_url'] ?>" alt="İlan Resmi" onerror="this.src='uploads/no-image.jpg'">
                        </div>
                        <div class="ad-info">
                             <h3><?= htmlspecialchars($ad['title']) ?></h3>
                            <div class="ad-price"><?= number_format($ad['price'], 0, ',', '.') ?> TL</div>
                            <div class="ad-location">
                                <?= $ad['city_name'] ?> / <?= $ad['district_name'] ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aradığınız kriterlere uygun ilan bulunamadı.</p>
            <?php endif; ?>
        </div>
    </main>

</div>
<div class="services-section">
        
        <div class="service-row">
            <h3>Oto360</h3>
            <div class="service-links">
                <a href="#" class="service-item"><i class="fa-solid fa-chart-line"></i> Araç Değerleme</a>
                <a href="#" class="service-item"><i class="fa-solid fa-arrow-right-arrow-left"></i> Araç Karşılaştırma</a>
                <a href="#" class="service-item"><i class="fa-solid fa-wrench"></i> Araç Hasar Sorgulama</a>
                <a href="#" class="service-item"><i class="fa-solid fa-file-contract"></i> Oto Ekspertiz</a>
                <a href="#" class="service-item"><i class="fa-solid fa-percent"></i> Kredi Teklifleri</a>
            </div>
        </div>

        <div class="service-row">
            <h3>Emlak360</h3>
            <div class="service-links">
                <a href="#" class="service-item"><i class="fa-solid fa-chart-pie"></i> Emlak Endeksi</a>
                <a href="#" class="service-item"><i class="fa-solid fa-building-user"></i> Gayrimenkul Ekspertiz</a>
                <a href="#" class="service-item"><i class="fa-solid fa-map"></i> Emlak Alım Rehberi</a>
                <a href="#" class="service-item"><i class="fa-solid fa-key"></i> Emlak Kiralama Rehberi</a>
            </div>
        </div>

        <div class="service-row" style="border:none;">
            <h3>Alışveriş Blog</h3>
            <div class="service-links">
                <a href="#" class="service-item"><i class="fa-solid fa-couch"></i> Ev & Yaşam</a>
                <a href="#" class="service-item"><i class="fa-solid fa-mobile-screen"></i> Teknoloji</a>
                <a href="#" class="service-item"><i class="fa-solid fa-shirt"></i> Moda</a>
                <a href="#" class="service-item"><i class="fa-solid fa-bicycle"></i> Spor & Outdoor</a>
            </div>
        </div>
    </div>

    <div class="seo-section">
        <div class="seo-container">
            
            <div class="seo-group">
                <h4>Popüler Alışveriş Markaları</h4>
                <div class="brand-grid">
                    <a href="index.php?search=Adidas" class="brand-box">Adidas</a>
                    <a href="index.php?search=Nike" class="brand-box">Nike</a>
                    <a href="index.php?search=Samsung" class="brand-box">Samsung</a>
                    <a href="index.php?search=Apple" class="brand-box">Apple</a>
                    <a href="index.php?search=Bosch" class="brand-box">Bosch</a>
                    <a href="index.php?search=Dyson" class="brand-box">Dyson</a>
                    <a href="index.php?search=Sony" class="brand-box">Sony</a>
                    <a href="index.php?search=Zara" class="brand-box">Zara</a>
                    <a href="index.php?search=Puma" class="brand-box">Puma</a>
                </div>
            </div>

            <div class="seo-group">
                <h4>Popüler Aramalar</h4>
                <div class="tag-list">
                    <a href="index.php?search=iPhone 14" class="tag-btn">iPhone 14</a>
                    <a href="index.php?search=Togg" class="tag-btn">Togg</a>
                    <a href="index.php?search=PlayStation 5" class="tag-btn">PlayStation 5</a>
                    <a href="index.php?search=Elektrikli Bisiklet" class="tag-btn">Elektrikli Bisiklet</a>
                    <a href="index.php?search=Laptop" class="tag-btn">Laptop</a>
                    <a href="index.php?search=Oyuncu Koltuğu" class="tag-btn">Oyuncu Koltuğu</a>
                    <a href="index.php?search=Çalışma Masası" class="tag-btn">Çalışma Masası</a>
                </div>
            </div>

            <div class="seo-group">
                <h4>Popüler Hizmetler</h4>
                <div class="tag-list">
                    <a href="#" class="tag-btn">Boyacı</a>
                    <a href="#" class="tag-btn">Evden Eve Nakliye</a>
                    <a href="#" class="tag-btn">Su Tesisatı</a>
                    <a href="#" class="tag-btn">Oto Ekspertiz</a>
                    <a href="#" class="tag-btn">Elektrikçi</a>
                    <a href="#" class="tag-btn">Özel Ders</a>
                </div>
            </div>

        </div>
    </div>

    <footer class="main-footer">
        <div class="footer-columns">
            <div class="f-col">
                <h4>Kurumsal</h4>
                <ul>
                    <li><a href="#">Hakkımızda</a></li>
                    <li><a href="#">Sürdürülebilirlik</a></li>
                    <li><a href="#">İnsan Kaynakları</a></li>
                    <li><a href="#">Haberler</a></li>
                    <li><a href="#">İletişim</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Hizmetlerimiz</h4>
                <ul>
                    <li><a href="#">Doping</a></li>
                    <li><a href="#">Param Güvende</a></li>
                    <li><a href="#">Güvenli e-Ticaret (GeT)</a></li>
                    <li><a href="#">Reklam</a></li>
                    <li><a href="#">Mobil</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Mağazalar</h4>
                <ul>
                    <li><a href="#">Neden Mağaza?</a></li>
                    <li><a href="#">Mağaza Açmak İstiyorum</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Gizlilik ve Kullanım</h4>
                <ul>
                    <li><a href="#">Güvenli Alışverişin İpuçları</a></li>
                    <li><a href="#">Sözleşmeler ve Kurallar</a></li>
                    <li><a href="#">Kullanım Koşulları</a></li>
                    <li><a href="#">Kişisel Verilerin Korunması</a></li>
                    <li><a href="#">Çerez Yönetimi</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Bizi Takip Edin</h4>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">X (Twitter)</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">YouTube</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="fb-content">
                <div class="contact-info">
                    <div class="c-item">
                        <div class="c-icon"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <div style="font-size:11px; color:#777;">7/24 Müşteri Hizmetleri</div>
                            <div>0 000 000 00 00</div>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon"><i class="fa-solid fa-circle-question"></i></div>
                        <div>
                            <div style="font-size:11px; color:#777;">Yardım Merkezi</div>
                            <div>berat@berattoraman.com.tr</div>
                        </div>
                    </div>
                </div>

                <div class="lang-select">
                    <span style="margin-right:10px;">Dil Seçimi:</span>
                    <select style="padding:5px;">
                        <option>Türkçe (Turkish)</option>
                        <option>English</option>
                    </select>
                </div>
            </div>
        </div>
    </footer>
<script src="script.js"></script>
</body>
</html>