<?php

require_once __DIR__ . '/../../includes/auth.php';
rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$akademisyen = $_SESSION["kullanici"] ?? "";

$stmt = $baglanti->prepare("
    SELECT DISTINCT
        o.ogrenciNo,
        o.ad,
        o.soyad,
        o.bolum

    FROM ogrenci o

    INNER JOIN ogrenci_ders od
        ON od.ogrenciNo = o.ogrenciNo

    INNER JOIN ders d
        ON d.dersKodu = od.dersKodu

    WHERE d.akademisyen = :akademisyen

    ORDER BY o.ad, o.soyad
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Öğrencilerim</title>

<link
    rel="stylesheet"
    href="/student_project/assets/css/style.css"
>

<style>

.table-card {
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 15px 18px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
}

tbody tr:hover {
    background: #f8fafc;
}

.student {
    display: flex;
    align-items: center;
    gap: 11px;
}

.student-avatar {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #dbeafe;
    color: #2563eb;

    font-weight: 700;
}

.student strong {
    font-size: 13px;
    color: #0f172a;
}

.empty {
    padding: 40px;
    text-align: center;
    color: #64748b;
}

</style>

</head>

<body>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="main">

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="content">

<div class="page-header">

<h1>Öğrencilerim</h1>

<p>
    Verdiğiniz derslerden en az birine kayıtlı
    öğrencileri görüntüleyebilirsiniz.
</p>

</div>


<div class="card table-card">

<?php if ($ogrenciler): ?>

<table>

<thead>

<tr>
    <th>Öğrenci</th>
    <th>Öğrenci No</th>
    <th>Bölüm</th>
</tr>

</thead>

<tbody>

<?php foreach ($ogrenciler as $ogrenci): ?>

<tr>

<td>

<div class="student">

<div class="student-avatar">

<?php
echo htmlspecialchars(
    mb_strtoupper(
        mb_substr($ogrenci["ad"], 0, 1, "UTF-8"),
        "UTF-8"
    )
);
?>

</div>

<strong>

<?php
echo htmlspecialchars(
    $ogrenci["ad"] . " " . $ogrenci["soyad"]
);
?>

</strong>

</div>

</td>

<td>
<?php echo htmlspecialchars($ogrenci["ogrenciNo"]); ?>
</td>

<td>
<?php echo htmlspecialchars($ogrenci["bolum"]); ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<div class="empty">
    Henüz derslerinize kayıtlı öğrenci bulunmamaktadır.
</div>

<?php endif; ?>

</div>

</main>
</div>

</body>
</html>