<?php

require_once __DIR__ . '/../../includes/auth.php';
rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$akademisyen = $_SESSION["kullanici"] ?? "";

$stmt = $baglanti->prepare("
    SELECT
        d.dersKodu,
        d.dersAdi,
        COUNT(DISTINCT od.ogrenciNo) AS ogrenciSayisi

    FROM ders d

    LEFT JOIN ogrenci_ders od
        ON od.dersKodu = d.dersKodu

    WHERE d.akademisyen = :akademisyen

    GROUP BY
        d.dersKodu,
        d.dersAdi

    ORDER BY d.dersAdi
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


$dersKodu = trim($_GET["ders"] ?? "");
$ogrenciler = [];


if ($dersKodu !== "") {

    $kontrol = $baglanti->prepare("
        SELECT COUNT(*)
        FROM ders
        WHERE dersKodu = :dersKodu
        AND akademisyen = :akademisyen
    ");

    $kontrol->execute([
        "dersKodu" => $dersKodu,
        "akademisyen" => $akademisyen
    ]);


    if ($kontrol->fetchColumn() > 0) {

        $stmt = $baglanti->prepare("
            SELECT
                o.ogrenciNo,
                o.ad,
                o.soyad,
                n.vize,
                n.final

            FROM ogrenci_ders od

            INNER JOIN ogrenci o
                ON o.ogrenciNo = od.ogrenciNo

            LEFT JOIN notlar n
                ON n.ogrenciNo = od.ogrenciNo
                AND n.dersKodu = od.dersKodu

            WHERE od.dersKodu = :dersKodu

            ORDER BY o.ad, o.soyad
        ");

        $stmt->execute([
            "dersKodu" => $dersKodu
        ]);

        $ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {

        $dersKodu = "";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Not İşlemleri</title>

<link
    rel="stylesheet"
    href="/student_project/assets/css/style.css"
>

<style>

.course-list {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.course-btn {
    padding: 10px 15px;
    border: 1px solid #cbd5e1;
    background: white;
    color: #334155;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.course-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.course-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: white;
}

.table-card {
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 14px 17px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
}

.grade-empty {
    color: #dc2626;
    font-weight: 600;
}

.btn {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 6px;
    background: #2563eb;
    color: white;
    font-size: 12px;
    font-weight: 600;
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

<h1>Not İşlemleri</h1>

<p>
    Önce ders seçerek o derse kayıtlı öğrencilerin
    notlarını görüntüleyebilir ve düzenleyebilirsiniz.
</p>

</div>


<div class="course-list">

<?php foreach ($dersler as $ders): ?>

<a
    href="?ders=<?php echo urlencode($ders["dersKodu"]); ?>"
    class="course-btn <?php echo $dersKodu === $ders["dersKodu"] ? "active" : ""; ?>"
>

<?php echo htmlspecialchars($ders["dersAdi"]); ?>

(<?php echo (int)$ders["ogrenciSayisi"]; ?>)

</a>

<?php endforeach; ?>

</div>


<?php if ($dersKodu !== ""): ?>

<div class="card table-card">

<?php if ($ogrenciler): ?>

<table>

<thead>

<tr>
    <th>Öğrenci No</th>
    <th>Ad Soyad</th>
    <th>Vize</th>
    <th>Final</th>
    <th>Ortalama</th>
    <th>İşlem</th>
</tr>

</thead>

<tbody>

<?php foreach ($ogrenciler as $ogrenci): ?>

<?php

$vize = $ogrenci["vize"];
$final = $ogrenci["final"];

$ortalama = null;

if ($vize !== null && $final !== null) {

    $ortalama =
        ((float)$vize * 0.40) +
        ((float)$final * 0.60);
}

?>

<tr>

<td>
<?php echo htmlspecialchars($ogrenci["ogrenciNo"]); ?>
</td>

<td>

<?php
echo htmlspecialchars(
    $ogrenci["ad"] . " " . $ogrenci["soyad"]
);
?>

</td>

<td>

<?php if ($vize !== null): ?>

<?php echo htmlspecialchars($vize); ?>

<?php else: ?>

<span class="grade-empty">Girilmedi</span>

<?php endif; ?>

</td>

<td>

<?php if ($final !== null): ?>

<?php echo htmlspecialchars($final); ?>

<?php else: ?>

<span class="grade-empty">Girilmedi</span>

<?php endif; ?>

</td>

<td>

<?php

echo $ortalama !== null
    ? number_format($ortalama, 2, ",", ".")
    : "-";

?>

</td>

<td>

<a
    class="btn"
    href="grade_edit.php?dersKodu=<?php
    echo urlencode($dersKodu);
    ?>&ogrenciNo=<?php
    echo urlencode($ogrenci["ogrenciNo"]);
    ?>"
>
    Not Düzenle
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<div class="empty">
    Bu derse kayıtlı öğrenci bulunmamaktadır.
</div>

<?php endif; ?>

</div>

<?php else: ?>

<div class="card empty">
    Not işlemlerine başlamak için yukarıdan bir ders seçin.
</div>

<?php endif; ?>

</main>
</div>

</body>
</html>