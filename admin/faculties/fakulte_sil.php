<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$fakulte = trim($_GET["fakulte"] ?? "");


if ($fakulte === "") {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// FAKÜLTE VAR MI?
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM fakulte
    WHERE `fakülte` = :fakulte
");

$stmt->execute([
    "fakulte" => $fakulte
]);


if ($stmt->fetchColumn() == 0) {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// BAĞLI BÖLÜM VAR MI?
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM bolum
    WHERE `fakülte` = :fakulte
");

$stmt->execute([
    "fakulte" => $fakulte
]);

$bolumSayisi = (int)$stmt->fetchColumn();


if ($bolumSayisi > 0) {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=fakulte_bolum_var"
    );

    exit;
}


// ==========================================
// SİL
// ==========================================

$stmt = $baglanti->prepare("
    DELETE FROM fakulte
    WHERE `fakülte` = :fakulte
");

$stmt->execute([
    "fakulte" => $fakulte
]);


header(
    "Location: /student_project/admin/faculties/index.php?durum=fakulte_silindi"
);

exit;