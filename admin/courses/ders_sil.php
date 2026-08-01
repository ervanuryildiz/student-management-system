<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$dersKodu =
    trim($_GET["dersKodu"] ?? "");


if ($dersKodu === "") {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


// DERS VAR MI?

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM ders
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);


if ($stmt->fetchColumn() == 0) {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


// ÖĞRENCİ KAYDI VAR MI?

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM ogrenci_ders
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);


if ($stmt->fetchColumn() > 0) {

    header(
        "Location: index.php?durum=ogrenci_var"
    );

    exit;
}


// NOT KAYDI VAR MI?

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM notlar
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);


if ($stmt->fetchColumn() > 0) {

    header(
        "Location: index.php?durum=ogrenci_var"
    );

    exit;
}


// DERSİ SİL

$stmt = $baglanti->prepare("
    DELETE FROM ders
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);


header(
    "Location: index.php?durum=silindi"
);

exit;