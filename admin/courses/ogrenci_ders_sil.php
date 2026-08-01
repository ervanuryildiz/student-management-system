<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$dersKodu =
    trim($_GET["dersKodu"] ?? "");

$ogrenciNo =
    trim($_GET["ogrenciNo"] ?? "");


if ($dersKodu === "" || $ogrenciNo === "") {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


try {

    $baglanti->beginTransaction();


    // Önce not kaydı

    $stmt = $baglanti->prepare("
        DELETE FROM notlar

        WHERE dersKodu = :dersKodu
        AND ogrenciNo = :ogrenciNo
    ");

    $stmt->execute([
        "dersKodu" => $dersKodu,
        "ogrenciNo" => $ogrenciNo
    ]);


    // Sonra ders kaydı

    $stmt = $baglanti->prepare("
        DELETE FROM ogrenci_ders

        WHERE dersKodu = :dersKodu
        AND ogrenciNo = :ogrenciNo
    ");

    $stmt->execute([
        "dersKodu" => $dersKodu,
        "ogrenciNo" => $ogrenciNo
    ]);


    $baglanti->commit();


    header(
        "Location: ogrenciler.php?dersKodu="
        . urlencode($dersKodu)
    );

    exit;


} catch (PDOException $e) {

    if ($baglanti->inTransaction()) {

        $baglanti->rollBack();
    }


    header(
        "Location: ogrenciler.php?dersKodu="
        . urlencode($dersKodu)
        . "&durum=hata"
    );

    exit;
}