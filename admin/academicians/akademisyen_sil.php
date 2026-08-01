<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$kullaniciAdi = trim($_GET["kullaniciAdi"] ?? "");

if ($kullaniciAdi === "") {
    header("Location: index.php?durum=bulunamadi");
    exit;
}


// Akademisyen var mı?
$stmt = $baglanti->prepare("
    SELECT *
    FROM admin
    WHERE kullaniciAdi = :kullaniciAdi
      AND unvan = 'akademisyen'
");

$stmt->execute([
    "kullaniciAdi" => $kullaniciAdi
]);

$akademisyen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$akademisyen) {
    header("Location: index.php?durum=bulunamadi");
    exit;
}


// Akademisyene bağlı ders var mı?
$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM ders
    WHERE akademisyen = :akademisyen
");

$stmt->execute([
    "akademisyen" => $kullaniciAdi
]);

$dersSayisi = (int)$stmt->fetchColumn();


if ($dersSayisi > 0) {

    header(
        "Location: index.php?durum=dersvar&adet=" .
        urlencode($dersSayisi)
    );

    exit;
}


// Akademisyeni sil
$stmt = $baglanti->prepare("
    DELETE FROM admin
    WHERE kullaniciAdi = :kullaniciAdi
      AND unvan = 'akademisyen'
");

$stmt->execute([
    "kullaniciAdi" => $kullaniciAdi
]);

header("Location: index.php?durum=silindi");
exit;