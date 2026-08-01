<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {

    header("Location: /student_project/login.php");
    exit;

}

require_once __DIR__ . '/../../database.php';


// =====================================================
// ÖĞRENCİ
// =====================================================

$ogrenciNo = trim(
    $_GET["ogrenciNo"] ?? ""
);


if ($ogrenciNo === "") {

    header("Location: index.php");
    exit;

}


$stmt = $baglanti->prepare("
    SELECT
        ogrenciNo,
        ad,
        soyad,
        bolum

    FROM ogrenci

    WHERE ogrenciNo = :ogrenciNo
");

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$ogrenci) {

    header("Location: index.php");
    exit;

}



// =====================================================
// DERS EKLE
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["ders_ekle"])
) {

    $dersKodu =
        trim($_POST["dersKodu"] ?? "");


    if ($dersKodu !== "") {


        // -------------------------------------------------
        // GÜVENLİK:
        // Ders gerçekten öğrencinin bölümüne mi ait?
        // -------------------------------------------------

        $kontrol = $baglanti->prepare("
            SELECT dersKodu

            FROM ders

            WHERE dersKodu = :dersKodu

            AND bolum = :bolum
        ");

        $kontrol->execute([

            "dersKodu" => $dersKodu,

            "bolum" => $ogrenci["bolum"]

        ]);


        if ($kontrol->fetch()) {


            // Aynı ders daha önce alınmış mı?

            $kayitKontrol =
                $baglanti->prepare("
                    SELECT *

                    FROM ogrenci_ders

                    WHERE ogrenciNo = :ogrenciNo

                    AND dersKodu = :dersKodu
                ");


            $kayitKontrol->execute([

                "ogrenciNo" => $ogrenciNo,

                "dersKodu" => $dersKodu

            ]);


            if (!$kayitKontrol->fetch()) {


                $ekle =
                    $baglanti->prepare("
                        INSERT INTO ogrenci_ders
                        (
                            ogrenciNo,
                            dersKodu
                        )

                        VALUES
                        (
                            :ogrenciNo,
                            :dersKodu
                        )
                    ");


                $ekle->execute([

                    "ogrenciNo" => $ogrenciNo,

                    "dersKodu" => $dersKodu

                ]);

            }

        }

    }


    header(
        "Location: dersler.php?ogrenciNo="
        . urlencode($ogrenciNo)
        . "&durum=eklendi"
    );

    exit;

}



// =====================================================
// DERS ÇIKAR
// =====================================================

if (
    isset($_GET["sil"])
    &&
    $_GET["sil"] !== ""
) {

    $dersKodu = trim($_GET["sil"]);


    try {

        $baglanti->beginTransaction();


        // Önce not

        $stmt = $baglanti->prepare("
            DELETE FROM notlar

            WHERE ogrenciNo = :ogrenciNo

            AND dersKodu = :dersKodu
        ");

        $stmt->execute([

            "ogrenciNo" => $ogrenciNo,

            "dersKodu" => $dersKodu

        ]);


        // Sonra ders kaydı

        $stmt = $baglanti->prepare("
            DELETE FROM ogrenci_ders

            WHERE ogrenciNo = :ogrenciNo

            AND dersKodu = :dersKodu
        ");

        $stmt->execute([

            "ogrenciNo" => $ogrenciNo,

            "dersKodu" => $dersKodu

        ]);


        $baglanti->commit();


    } catch (PDOException $e) {

        if ($baglanti->inTransaction()) {

            $baglanti->rollBack();

        }

    }


    header(
        "Location: dersler.php?ogrenciNo="
        . urlencode($ogrenciNo)
        . "&durum=silindi"
    );

    exit;

}



// =====================================================
// ÖĞRENCİNİN ALDIĞI DERSLER
// =====================================================

$stmt = $baglanti->prepare("
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.akademisyen

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON od.dersKodu = d.dersKodu

    WHERE od.ogrenciNo = :ogrenciNo

    ORDER BY d.dersKodu
");

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$alinanDersler =
    $stmt->fetchAll(PDO::FETCH_ASSOC);



// =====================================================
// ALABİLECEĞİ DERSLER
// =====================================================

$stmt = $baglanti->prepare("
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.akademisyen

    FROM ders d

    WHERE d.bolum = :bolum

    AND NOT EXISTS
    (
        SELECT 1

        FROM ogrenci_ders od

        WHERE od.ogrenciNo = :ogrenciNo

        AND od.dersKodu = d.dersKodu
    )

    ORDER BY d.dersKodu
");

$stmt->execute([

    "bolum" => $ogrenci["bolum"],

    "ogrenciNo" => $ogrenciNo

]);

$alinabilirDersler =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Dersleri Yönet</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        'Segoe UI',
        Arial,
        sans-serif;

    background: #f5f7fb;

    color: #172033;

}

.header {

    background: #0f172a;

    color: white;

    padding: 20px 40px;

}

.container {

    max-width: 1100px;

    margin: 35px auto;

    padding: 0 20px;

}

.back {

    display: inline-block;

    color: #2563eb;

    text-decoration: none;

    margin-bottom: 20px;

}

.student-card {

    background: white;

    padding: 22px;

    border-radius: 12px;

    border: 1px solid #e5eaf1;

    margin-bottom: 22px;

}

.student-card h1 {

    margin: 0 0 7px;

}

.student-card p {

    margin: 4px 0;

    color: #64748b;

}

.grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 22px;

}

.card {

    background: white;

    border:
        1px solid #e5eaf1;

    border-radius: 12px;

    overflow: hidden;

}

.card-header {

    padding: 18px 20px;

    border-bottom:
        1px solid #e5eaf1;

}

.card-header h2 {

    margin: 0 0 5px;

    font-size: 17px;

}

.card-header p {

    margin: 0;

    color: #64748b;

    font-size: 12px;

}

.card-body {

    padding: 20px;

}

.course {

    border:
        1px solid #e5eaf1;

    border-radius: 9px;

    padding: 14px;

    margin-bottom: 10px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

}

.course:last-child {

    margin-bottom: 0;

}

.course strong {

    display: block;

    margin-bottom: 4px;

}

.course span {

    color: #64748b;

    font-size: 12px;

}

.btn {

    display: inline-block;

    border: none;

    border-radius: 7px;

    padding: 8px 12px;

    cursor: pointer;

    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;

}

.btn-add {

    background: #dcfce7;

    color: #166534;

}

.btn-delete {

    background: #fee2e2;

    color: #991b1b;

}

.empty {

    background: #f8fafc;

    color: #64748b;

    text-align: center;

    padding: 25px;

    border-radius: 8px;

}

.message {

    background: #dcfce7;

    color: #166534;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

}

@media(max-width:800px) {

    .grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<div class="header">

    <strong>
        📚 Öğrenci Ders Yönetimi
    </strong>

</div>


<div class="container">


<a
    href="index.php"
    class="back"
>
    ← Öğrenci Listesine Dön
</a>


<div class="student-card">

<h1>

<?php
echo htmlspecialchars(
    $ogrenci["ad"]
    . " "
    . $ogrenci["soyad"]
);
?>

</h1>


<p>

<strong>Öğrenci No:</strong>

<?php
echo htmlspecialchars(
    $ogrenci["ogrenciNo"]
);
?>

</p>


<p>

<strong>Bölüm:</strong>

<?php
echo htmlspecialchars(
    $ogrenci["bolum"]
);
?>

</p>

</div>


<?php if (isset($_GET["durum"])): ?>

<div class="message">

<?php

if ($_GET["durum"] === "eklendi") {

    echo "✓ Ders öğrenciye başarıyla eklendi.";

} elseif ($_GET["durum"] === "silindi") {

    echo "✓ Ders öğrenciden kaldırıldı.";

}

?>

</div>

<?php endif; ?>



<div class="grid">


<!-- ===============================================
     ALDIĞI DERSLER
=============================================== -->

<div class="card">


<div class="card-header">

<h2>
    📘 Aldığı Dersler
</h2>

<p>
    Öğrencinin şu anda kayıtlı olduğu dersler
</p>

</div>


<div class="card-body">


<?php if (count($alinanDersler) > 0): ?>


<?php foreach ($alinanDersler as $ders): ?>


<div class="course">


<div>

<strong>

<?php
echo htmlspecialchars(
    $ders["dersKodu"]
    . " - "
    . $ders["dersAdi"]
);
?>

</strong>


<span>

Akademisyen:

<?php

echo htmlspecialchars(
    !empty($ders["akademisyen"])
        ? $ders["akademisyen"]
        : "Atanmadı"
);

?>

</span>

</div>


<a
    href="dersler.php?ogrenciNo=<?php
    echo urlencode($ogrenciNo);
    ?>&sil=<?php
    echo urlencode($ders["dersKodu"]);
    ?>"
    class="btn btn-delete"

    onclick="
        return confirm(
            'Bu dersi öğrenciden kaldırmak istediğinize emin misiniz? Öğrencinin bu derse ait notları da silinecektir.'
        );
    "
>
    Kaldır
</a>


</div>


<?php endforeach; ?>


<?php else: ?>


<div class="empty">

    Öğrenci henüz herhangi bir ders almıyor.

</div>


<?php endif; ?>


</div>

</div>



<!-- ===============================================
     ALABİLECEĞİ DERSLER
=============================================== -->

<div class="card">


<div class="card-header">

<h2>
    ➕ Alabileceği Dersler
</h2>

<p>

Yalnızca

<strong>

<?php
echo htmlspecialchars(
    $ogrenci["bolum"]
);
?>

</strong>

bölümüne ait dersler gösterilmektedir.

</p>

</div>


<div class="card-body">


<?php if (count($alinabilirDersler) > 0): ?>


<?php foreach ($alinabilirDersler as $ders): ?>


<div class="course">


<div>

<strong>

<?php

echo htmlspecialchars(
    $ders["dersKodu"]
    . " - "
    . $ders["dersAdi"]
);

?>

</strong>


<span>

Akademisyen:

<?php

echo htmlspecialchars(
    !empty($ders["akademisyen"])
        ? $ders["akademisyen"]
        : "Atanmadı"
);

?>

</span>

</div>



<form method="POST">

<input
    type="hidden"
    name="dersKodu"
    value="<?php
    echo htmlspecialchars(
        $ders["dersKodu"]
    );
    ?>"
>

<button
    type="submit"
    name="ders_ekle"
    class="btn btn-add"
>
    + Ekle
</button>

</form>


</div>


<?php endforeach; ?>


<?php else: ?>


<div class="empty">

    Öğrencinin bölümüne ait eklenebilecek başka ders bulunmamaktadır.

</div>


<?php endif; ?>


</div>

</div>


</div>

</div>

</body>

</html>