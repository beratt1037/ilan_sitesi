<?php
require 'db.php';
$city_id = $_GET['city_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM districts WHERE city_id = ? ORDER BY title ASC");
$stmt->execute([$city_id]);
$districts = $stmt->fetchAll();

echo '<option value="">İlçe Seçiniz</option>';
foreach ($districts as $d) {
    echo '<option value="'.$d['id'].'">'.htmlspecialchars($d['title']).'</option>';
}
?>