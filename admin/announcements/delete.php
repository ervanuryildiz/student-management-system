<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);


if (!$id) {

    header("Location: index.php");
    exit;
}


$stmt = $baglanti->prepare("
    DELETE FROM duyuru
    WHERE duyuruId = :id
");


$stmt->execute([
    "id" => $id
]);


header("Location: index.php?durum=silindi");
exit;