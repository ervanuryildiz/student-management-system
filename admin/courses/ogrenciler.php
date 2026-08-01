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


// DERS

$stmt = $baglanti->prepare("
    SELECT *
    FROM ders
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);

$ders = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$ders) {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


// ÖĞRENCİLER

$stmt = $baglanti->prepare("
    SELECT
        o.ogrenciNo,
        o.ad,
        o.soyad,
        o.bolum,

        n.vize,
        n.final

    FROM ogrenci_ders od

    INNER JOIN ogrenci o
        ON o.ogrenciNo = od.ogrenciNo

    LEFT JOIN notlar n
        ON n.ogrenciNo = od.ogrenciNo
        AND n.dersKodu = od.dersKodu

    WHERE od.dersKodu = :dersKodu

    ORDER BY o.ad ASC, o.soyad ASC
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);

$ogrenciler =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


function harfNotu($ortalama, $final)
{
    if ($final < 50) {
        return "FF";
    }

    if ($ortalama >= 90) return "AA";
    if ($ortalama >= 85) return "BA";
    if ($ortalama >= 80) return "BB";
    if ($ortalama >= 75) return "CB";
    if ($ortalama >= 70) return "CC";
    if ($ortalama >= 65) return "DC";
    if ($ortalama >= 60) return "DD";
    if ($ortalama >= 50) return "FD";

    return "FF";
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ders Öğrencileri</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family: "Segoe UI", Arial, sans-serif;

    background: #f4f7fb;

    color: #1e293b;
}

.header {
    background: #0f172a;

    color: white;

    padding: 20px 35px;
}

.container {
    max-width: 1200px;

    margin: 35px auto;

    padding: 0 20px;
}

.course-card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 14px;

    padding: 24px;

    margin-bottom: 20px;
}

.course-code {
    color: #2563eb;

    font-weight: 700;

    font-size: 14px;
}

.course-card h1 {
    margin: 7px 0;
}

.course-card p {
    margin: 0;

    color: #64748b;
}

.card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 14px;

    padding: 24px;
}

.toolbar {
    margin-bottom: 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 10px;
}

.add {
    background: #16a34a;
    color: white;
}

.add:hover {
    background: #15803d;
}

.btn {
    display: inline-block;

    padding: 9px 13px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;
}

.back {
    background: #64748b;
    color: white;
}

.delete {
    background: #fee2e2;
    color: #991b1b;
}

table {
    width: 100%;

    border-collapse: collapse;
}

th,
td {
    padding: 13px;

    border-bottom: 1px solid #e2e8f0;

    text-align: left;
}

th {
    background: #f8fafc;

    color: #475569;

    font-size: 12px;
}

.badge {
    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;
}

.pass {
    background: #dcfce7;
    color: #166534;
}

.fail {
    background: #fee2e2;
    color: #991b1b;
}

.pending {
    background: #e2e8f0;
    color: #475569;
}

.success-message {
    background: #dcfce7;
    color: #166534;

    border: 1px solid #bbf7d0;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;
    font-weight: 500;
}



</style>

</head>

<body>


<div class="header">

<strong>
🎓 Üniversite Öğrenci Takip Sistemi
</strong>

</div>


<div class="container">


<div class="course-card">

<div class="course-code">

<?php
echo htmlspecialchars(
    $ders["dersKodu"]
);
?>

</div>

<h1>

<?php
echo htmlspecialchars(
    $ders["dersAdi"]
);
?>

</h1>

<p>

Akademisyen:

<strong>

<?php
echo !empty($ders["akademisyen"])
    ? htmlspecialchars($ders["akademisyen"])
    : "Atanmadı";
?>

</strong>

</p>

</div>


<div class="card">

<?php if (
    isset($_GET["durum"])
    && $_GET["durum"] === "ogrenci_eklendi"
): ?>

    <div class="success-message">
        ✓ Öğrenci derse başarıyla kaydedildi.
    </div>

<?php endif; ?>


<div class="toolbar">

    <a
        href="index.php"
        class="btn back"
    >
        ← Derslere Dön
    </a>

    <a
        href="ogrenci_ekle.php?dersKodu=<?php
        echo urlencode($dersKodu);
        ?>"
        class="btn add"
    >
        + Derse Öğrenci Ekle
    </a>

</div>


<table>

<thead>

<tr>

<th>Öğrenci No</th>

<th>Ad Soyad</th>

<th>Bölüm</th>

<th>Vize</th>

<th>Final</th>

<th>Ortalama</th>

<th>Harf</th>

<th>Durum</th>

<th>İşlem</th>

</tr>

</thead>


<tbody>


<?php if (count($ogrenciler) > 0): ?>


<?php foreach ($ogrenciler as $ogrenci): ?>


<?php

$ortalama = null;
$harf = "-";
$durum = "Not Bekleniyor";
$class = "pending";


if (
    $ogrenci["vize"] !== null
    &&
    $ogrenci["final"] !== null
) {

    $ortalama =
        ($ogrenci["vize"] * 0.40)
        +
        ($ogrenci["final"] * 0.60);


    $harf =
        harfNotu(
            $ortalama,
            $ogrenci["final"]
        );


    if (
        $ogrenci["final"] < 50
        ||
        $ortalama < 50
    ) {

        $durum = "Kaldı";
        $class = "fail";

    } else {

        $durum = "Geçti";
        $class = "pass";
    }
}

?>


<tr>


<td>

<?php
echo htmlspecialchars(
    $ogrenci["ogrenciNo"]
);
?>

</td>


<td>

<strong>

<?php

echo htmlspecialchars(
    $ogrenci["ad"]
    . " "
    . $ogrenci["soyad"]
);

?>

</strong>

</td>


<td>

<?php
echo htmlspecialchars(
    $ogrenci["bolum"]
);
?>

</td>


<td>

<?php
echo $ogrenci["vize"] !== null
    ? htmlspecialchars($ogrenci["vize"])
    : "-";
?>

</td>


<td>

<?php
echo $ogrenci["final"] !== null
    ? htmlspecialchars($ogrenci["final"])
    : "-";
?>

</td>


<td>

<?php

echo $ortalama !== null
    ? number_format($ortalama, 2)
    : "-";

?>

</td>


<td>

<strong>
<?php echo $harf; ?>
</strong>

</td>


<td>

<span class="badge <?php echo $class; ?>">

<?php echo $durum; ?>

</span>

</td>


<td>

<a
    href="ogrenci_ders_sil.php?dersKodu=<?php
    echo urlencode($dersKodu);
    ?>&ogrenciNo=<?php
    echo urlencode($ogrenci["ogrenciNo"]);
    ?>"
    class="btn delete"

    onclick="return confirm(
        'Öğrenciyi bu dersten çıkarmak istediğinize emin misiniz?'
    );"
>
Dersten Çıkar
</a>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>

<tr>

<td colspan="9">

Bu derse kayıtlı öğrenci bulunmamaktadır.

</td>

</tr>

<?php endif; ?>


</tbody>

</table>


</div>

</div>

</body>

</html>