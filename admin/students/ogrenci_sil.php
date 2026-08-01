<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {

    header("Location: /student_project/login.php");
    exit;

}

require_once __DIR__ . '/../../database.php';


if (
    !isset($_GET["ogrenciNo"]) ||
    trim($_GET["ogrenciNo"]) === ""
) {

    header("Location: index.php");
    exit;

}


$ogrenciNo = trim($_GET["ogrenciNo"]);


try {

    $baglanti->beginTransaction();


    // Öğrencinin notlarını sil

    $stmt = $baglanti->prepare("
        DELETE FROM notlar
        WHERE ogrenciNo = :ogrenciNo
    ");

    $stmt->execute([
        "ogrenciNo" => $ogrenciNo
    ]);


    // Ders kayıtlarını sil

    $stmt = $baglanti->prepare("
        DELETE FROM ogrenci_ders
        WHERE ogrenciNo = :ogrenciNo
    ");

    $stmt->execute([
        "ogrenciNo" => $ogrenciNo
    ]);


    // Öğrenciyi sil

    $stmt = $baglanti->prepare("
        DELETE FROM ogrenci
        WHERE ogrenciNo = :ogrenciNo
    ");

    $stmt->execute([
        "ogrenciNo" => $ogrenciNo
    ]);


    $baglanti->commit();


    header(
        "Location: index.php?durum=silindi"
    );

    exit;


} catch (PDOException $e) {


    if ($baglanti->inTransaction()) {

        $baglanti->rollBack();

    }


    header(
        "Location: index.php?durum=hata"
    );

    exit;

}