<?php
// ajax_districts.php
require 'db.php'; // Senin görselindeki berateuy_sahibinden ayarlı db.php

$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;

$stmt = $pdo->prepare("SELECT id, title FROM districts WHERE city_id = ? ORDER BY title ASC");
$stmt->execute([$city_id]);
$districts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// BU SATIR ÇOK ÖNEMLİ: JS'nin veriyi tanımasını sağlar
header('Content-Type: application/json');
echo json_encode($districts);
exit;