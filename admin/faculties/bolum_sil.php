<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$bolum =
    trim($_GET["bolum"] ?? "");

$fakulte =
    trim($_GET["fakulte"] ?? "");


if ($bolum === "" || $fakulte === "") {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// KAYIT VAR MI?
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM bolum

    WHERE `bölüm` = :bolum
    AND `fakülte` = :fakulte
");

$stmt->execute([
    "bolum" => $bolum,
    "fakulte" => $fakulte
]);


if ($stmt->fetchColumn() == 0) {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// BÖLÜMÜ SİL
// ==========================================

$stmt = $baglanti->prepare("
    DELETE FROM bolum

    WHERE `bölüm` = :bolum
    AND `fakülte` = :fakulte
");

$stmt->execute([
    "bolum" => $bolum,
    "fakulte" => $fakulte
]);


header(
    "Location: /student_project/admin/faculties/index.php?durum=bolum_silindi"
);

exit;