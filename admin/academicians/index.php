<?php
session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . "/../../database.php";

$stmt = $baglanti->query("
SELECT
    kullaniciAdi,
    ad,
    soyad,
    bolum,
    unvan
FROM admin
WHERE unvan='akademisyen'
ORDER BY ad, soyad
");

$akademisyenler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>

<meta charset="UTF-8">
<title>Akademisyenler</title>

<link rel="stylesheet"
href="/student_project/assets/css/style.css">

<style>

table{
width:100%;
border-collapse:collapse;
background:white;
}

th,td{
padding:14px;
border-bottom:1px solid #ddd;
text-align:left;
}

th{
background:#f5f5f5;
}

.btn{
    display: inline-block;
    min-width: 85px;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    transition: 0.2s;
}

.btn:hover{
    opacity: .9;
}

.ekle{
    background:#16a34a;
}

.duzenle{
    background:#2563eb;
}

.sil{
    background:#dc2626;
}

.top{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

</style>

</head>

<body>

<?php require_once __DIR__ . "/../../includes/sidebar.php"; ?>

<div class="main">

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<main class="content">

<div class="top">

<h2>Akademisyenler</h2>

<a class="btn ekle"
href="akademisyen_ekle.php">

+ Yeni Akademisyen

</a>

</div>

<table>

<tr>

<th>Ad Soyad</th>
<th>Kullanıcı Adı</th>
<th>Bölüm</th>
<th>İşlemler</th>

</tr>

<?php foreach($akademisyenler as $a): ?>

<tr>

<td>

<?= htmlspecialchars($a["ad"]." ".$a["soyad"]) ?>

</td>

<td>

<?= htmlspecialchars($a["kullaniciAdi"]) ?>

</td>

<td>

<?= htmlspecialchars($a["bolum"]) ?>

</td>

<td>

<a
    class="btn duzenle"
    href="akademisyen_duzenle.php?kullaniciAdi=<?= urlencode($a["kullaniciAdi"]) ?>">
    Düzenle
</a>

<a
    class="btn sil"
    href="akademisyen_sil.php?kullaniciAdi=<?= urlencode($a["kullaniciAdi"]) ?>"
    onclick="return confirm('Silinsin mi?')">
    Sil
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</main>

</div>

</body>
</html>