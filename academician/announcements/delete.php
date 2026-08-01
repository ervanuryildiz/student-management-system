<?php

require_once __DIR__ . '/../../includes/auth.php';

rolKontrol("akademisyen");


require_once __DIR__ . '/../../database.php';


$akademisyen =
    $_SESSION["kullanici"] ?? "";


// ==========================================
// SADECE POST İSTEĞİ
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: index.php?tab=yayinlanan"
    );

    exit;
}


// ==========================================
// DUYURU ID
// ==========================================

$duyuruId =
    filter_input(
        INPUT_POST,
        "id",
        FILTER_VALIDATE_INT
    );


if (!$duyuruId) {

    header(
        "Location: index.php?tab=yayinlanan&durum=hata"
    );

    exit;
}


// ==========================================
// SADECE KENDİ DUYURUSUNU SİLEBİLİR
// ==========================================

$stmt = $baglanti->prepare("
    DELETE FROM duyuru

    WHERE duyuruId = :duyuruId

    AND yayinlayan = :yayinlayan

    AND yayinlayanRol = 'akademisyen'
");


$stmt->execute([

    "duyuruId" => $duyuruId,

    "yayinlayan" => $akademisyen

]);


// ==========================================
// SONUÇ
// ==========================================

if ($stmt->rowCount() > 0) {

    header(
        "Location: index.php?tab=yayinlanan&durum=silindi"
    );

} else {

    header(
        "Location: index.php?tab=yayinlanan&durum=yetkisiz"
    );
}

exit;