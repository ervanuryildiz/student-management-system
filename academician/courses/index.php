<?php

// ==========================================
// YETKİ KONTROLÜ
// ==========================================

require_once __DIR__ . '/../../includes/auth.php';

rolKontrol("akademisyen");


// ==========================================
// VERİTABANI
// ==========================================

require_once __DIR__ . '/../../database.php';


// ==========================================
// AKADEMİSYEN
// ==========================================

$akademisyen = $_SESSION["kullanici"] ?? "";


// ==========================================
// AKADEMİSYENİN DERSLERİNİ GETİR
// ==========================================

$sql = "
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.bolum,
        COUNT(DISTINCT od.ogrenciNo) AS ogrenciSayisi

    FROM ders d

    LEFT JOIN ogrenci_ders od
        ON d.dersKodu = od.dersKodu

    WHERE d.akademisyen = :akademisyen

    GROUP BY
        d.dersKodu,
        d.dersAdi,
        d.bolum

    ORDER BY d.dersKodu ASC
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// SEÇİLEN DERS
// ==========================================

$secilenDersKodu = trim($_GET["dersKodu"] ?? "");

$secilenDers = null;

$ogrenciler = [];


// ==========================================
// DERS SEÇİLDİYSE
// ==========================================

if ($secilenDersKodu !== "") {

    // ======================================
    // DERS AKADEMİSYENE AİT Mİ?
    // ======================================

    $stmt = $baglanti->prepare("
        SELECT
            dersKodu,
            dersAdi,
            bolum

        FROM ders

        WHERE dersKodu = :dersKodu

        AND akademisyen = :akademisyen

        LIMIT 1
    ");

    $stmt->execute([
        "dersKodu" => $secilenDersKodu,
        "akademisyen" => $akademisyen
    ]);

    $secilenDers = $stmt->fetch(PDO::FETCH_ASSOC);


    // ======================================
    // DERS BULUNDUYSA ÖĞRENCİ + NOT GETİR
    // ======================================

    if ($secilenDers) {

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

            ORDER BY
                o.ad ASC,
                o.soyad ASC
        ");

        $stmt->execute([
            "dersKodu" => $secilenDersKodu
        ]);

        $ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<!DOCTYPE html>

<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Derslerim</title>


    <!-- ORTAK CSS -->

    <link
        rel="stylesheet"
        href="/student_project/assets/css/style.css"
    >


    <style>

        /* =====================================
           DERSLERİM SAYFASI
        ===================================== */

        .table-card {
            overflow: hidden;

            margin-bottom: 25px;
        }


        .table-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 20px 22px;

            border-bottom: 1px solid #e2e8f0;
        }


        .table-header h2 {
            margin: 0 0 5px;

            color: #0f172a;

            font-size: 17px;
        }


        .table-header p {
            margin: 0;

            color: #64748b;

            font-size: 12px;
        }


        .summary {
            color: #64748b;

            font-size: 13px;

            white-space: nowrap;
        }


        .summary strong {
            color: #0f172a;
        }


        /* =====================================
           TABLO
        ===================================== */

        .table-wrapper {
            overflow-x: auto;
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

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .3px;
        }


        td {
            color: #334155;

            font-size: 13px;
        }


        tbody tr {
            transition: .15s;
        }


        tbody tr:hover {
            background: #f8fafc;
        }


        tbody tr:last-child td {
            border-bottom: none;
        }


        /* =====================================
           DERS BİLGİLERİ
        ===================================== */

        .course-code {
            display: inline-block;

            padding: 6px 9px;

            background: #eff6ff;

            color: #1d4ed8;

            border-radius: 6px;

            font-size: 11px;

            font-weight: 700;
        }


        .course-name {
            color: #0f172a;

            font-weight: 600;
        }


        .student-count {
            display: inline-block;

            padding: 6px 10px;

            background: #f1f5f9;

            color: #475569;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 600;
        }


        /* =====================================
           BUTONLAR
        ===================================== */

        .btn-view {
            display: inline-block;

            padding: 8px 12px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-size: 11px;

            font-weight: 600;

            transition: .2s;
        }


        .btn-view:hover {
            background: #1d4ed8;
        }


        .btn-close {
            display: inline-block;

            padding: 8px 12px;

            background: #f1f5f9;

            color: #475569;

            text-decoration: none;

            border-radius: 6px;

            font-size: 11px;

            font-weight: 600;
        }


        .btn-close:hover {
            background: #e2e8f0;
        }


        /* =====================================
           ÖĞRENCİ
        ===================================== */

        .student-info {
            display: flex;

            align-items: center;

            gap: 10px;
        }


        .student-avatar {
            width: 36px;
            height: 36px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background: #dbeafe;

            color: #2563eb;

            border-radius: 50%;

            font-size: 12px;

            font-weight: 700;
        }


        .student-info strong {
            display: block;

            color: #0f172a;

            font-size: 13px;
        }


        .student-info span {
            display: block;

            margin-top: 2px;

            color: #94a3b8;

            font-size: 11px;
        }


        /* =====================================
           NOTLAR
        ===================================== */

        .grade {
            display: inline-block;

            min-width: 42px;

            padding: 6px 9px;

            background: #f1f5f9;

            color: #334155;

            border-radius: 6px;

            text-align: center;

            font-weight: 600;
        }


        .grade-empty {
            color: #94a3b8;

            font-size: 11px;
        }


        .average {
            display: inline-block;

            min-width: 55px;

            padding: 6px 9px;

            background: #ecfdf5;

            color: #15803d;

            border-radius: 6px;

            text-align: center;

            font-weight: 700;
        }


        /* =====================================
           SEÇİLEN DERS BİLGİSİ
        ===================================== */

        .selected-course-info {
            display: flex;

            gap: 25px;

            flex-wrap: wrap;

            padding: 14px 22px;

            background: #f8fafc;

            border-bottom: 1px solid #e2e8f0;
        }


        .selected-info-item span {
            display: block;

            margin-bottom: 3px;

            color: #94a3b8;

            font-size: 10px;

            text-transform: uppercase;
        }


        .selected-info-item strong {
            color: #334155;

            font-size: 12px;
        }


        /* =====================================
           BOŞ
        ===================================== */

        .empty {
            padding: 45px 25px;

            color: #64748b;

            text-align: center;

            font-size: 13px;
        }

        .letter-grade {
    display: inline-block;

    min-width: 42px;

    padding: 6px 10px;

    background: #eff6ff;

    color: #1d4ed8;

    border-radius: 6px;

    text-align: center;

    font-weight: 700;
}


.status {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;
}


.status-success {
    background: #dcfce7;
    color: #15803d;
}


.status-danger {
    background: #fee2e2;
    color: #b91c1c;
}


.status-waiting {
    background: #f1f5f9;
    color: #64748b;
}


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media(max-width: 700px) {

            .table-header {
                align-items: flex-start;

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     ORTAK SIDEBAR
=========================================== -->

<?php
require_once __DIR__ . '/../../includes/sidebar.php';
?>


<div class="main">


    <!-- ======================================
         ORTAK HEADER
    ======================================= -->

    <?php
    require_once __DIR__ . '/../../includes/header.php';
    ?>


    <main class="content">


        <!-- ==================================
             SAYFA BAŞLIĞI
        =================================== -->

        <div class="page-header">

            <h1>
                Derslerim
            </h1>

            <p>
                Size atanmış dersleri ve bu derslere
                kayıtlı öğrencileri görüntüleyebilirsiniz.
            </p>

        </div>


        <!-- ==================================
             DERS LİSTESİ
        =================================== -->

        <div class="card table-card">


            <div class="table-header">


                <div>

                    <h2>
                        Ders Listesi
                    </h2>

                    <p>
                        Öğrencileri ve notlarını görmek
                        için ilgili dersi seçin.
                    </p>

                </div>


                <div class="summary">

                    Toplam

                    <strong>
                        <?php echo count($dersler); ?>
                    </strong>

                    ders

                </div>


            </div>


            <?php if (count($dersler) > 0): ?>


                <div class="table-wrapper">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Ders Kodu
                                </th>

                                <th>
                                    Ders Adı
                                </th>

                                <th>
                                    Bölüm
                                </th>

                                <th>
                                    Kayıtlı Öğrenci
                                </th>

                                <th>
                                    İşlem
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach ($dersler as $ders): ?>


                                <tr>


                                    <!-- DERS KODU -->

                                    <td>

                                        <span class="course-code">

                                            <?php

                                            echo htmlspecialchars(
                                                $ders["dersKodu"]
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <!-- DERS ADI -->

                                    <td>

                                        <span class="course-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $ders["dersAdi"]
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <!-- BÖLÜM -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $ders["bolum"]
                                        );

                                        ?>

                                    </td>


                                    <!-- ÖĞRENCİ SAYISI -->

                                    <td>

                                        <span class="student-count">

                                            👥

                                            <?php

                                            echo (int)
                                                $ders["ogrenciSayisi"];

                                            ?>

                                            öğrenci

                                        </span>

                                    </td>


                                    <!-- İŞLEM -->

                                    <td>

                                        <a
                                            href="index.php?dersKodu=<?php
                                            echo urlencode(
                                                $ders["dersKodu"]
                                            );
                                            ?>"
                                            class="btn-view"
                                        >
                                            Öğrencileri Gör
                                        </a>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <div class="empty">

                    Henüz size atanmış bir ders
                    bulunmamaktadır.

                </div>


            <?php endif; ?>


        </div>


        <!-- ==================================
             SEÇİLEN DERSİN ÖĞRENCİLERİ
        =================================== -->

        <?php if ($secilenDers): ?>


            <div class="card table-card">


                <!-- BAŞLIK -->

                <div class="table-header">


                    <div>

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $secilenDers["dersAdi"]
                            );

                            ?>

                            - Öğrenciler

                        </h2>


                        <p>
                            Öğrencilerin mevcut notları
                            yalnızca görüntülenmektedir.
                        </p>

                    </div>


                    <a
                        href="index.php"
                        class="btn-close"
                    >
                        Kapat
                    </a>


                </div>


                <!-- DERS BİLGİLERİ -->

                <div class="selected-course-info">


                    <div class="selected-info-item">

                        <span>
                            Ders Kodu
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $secilenDers["dersKodu"]
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="selected-info-item">

                        <span>
                            Bölüm
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $secilenDers["bolum"]
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="selected-info-item">

                        <span>
                            Öğrenci Sayısı
                        </span>

                        <strong>

                            <?php
                            echo count($ogrenciler);
                            ?>

                        </strong>

                    </div>


                </div>


                <!-- ÖĞRENCİ TABLOSU -->

                <?php if (count($ogrenciler) > 0): ?>


                    <div class="table-wrapper">


                        <table>


                            <thead>

    <tr>

        <th>Öğrenci</th>
        <th>Öğrenci No</th>
        <th>Bölüm</th>
        <th>Vize</th>
        <th>Final</th>
        <th>Ortalama</th>
        <th>Harf Notu</th>
        <th>Durum</th>

    </tr>

</thead>

                            <tbody>


                                <?php foreach ($ogrenciler as $ogrenci): ?>


                                    <?php

                                    // ==============================
                                    // NOTLAR
                                    // ==============================

                                    $vize = $ogrenci["vize"];
$final = $ogrenci["final"];

$ortalama = null;
$harfNotu = "-";
$durum = "-";


// Vize ve final girilmişse hesapla
if ($vize !== null && $final !== null) {

    // %40 Vize + %60 Final
    $ortalama =
        ((float)$vize * 0.40)
        +
        ((float)$final * 0.60);


    // ======================================
    // HARF NOTU
    // ======================================

    if ($ortalama >= 90) {

        $harfNotu = "AA";

    } elseif ($ortalama >= 85) {

        $harfNotu = "BA";

    } elseif ($ortalama >= 80) {

        $harfNotu = "BB";

    } elseif ($ortalama >= 75) {

        $harfNotu = "CB";

    } elseif ($ortalama >= 70) {

        $harfNotu = "CC";

    } elseif ($ortalama >= 65) {

        $harfNotu = "DC";

    } elseif ($ortalama >= 60) {

        $harfNotu = "DD";

    } elseif ($ortalama >= 50) {

        $harfNotu = "FD";

    } else {

        $harfNotu = "FF";
    }


    // ======================================
    // GEÇTİ / KALDI
    // ======================================

    if ($ortalama >= 60) {

        $durum = "Geçti";

    } else {

        $durum = "Kaldı";
    }
}

                                    ?>


                                    <tr>


                                        <!-- ÖĞRENCİ -->

                                        <td>


                                            <div class="student-info">


                                                <div class="student-avatar">

                                                    <?php

                                                    echo htmlspecialchars(
                                                        mb_strtoupper(
                                                            mb_substr(
                                                                $ogrenci["ad"],
                                                                0,
                                                                1,
                                                                "UTF-8"
                                                            ),
                                                            "UTF-8"
                                                        )
                                                    );

                                                    ?>

                                                </div>


                                                <div>


                                                    <strong>

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $ogrenci["ad"]
                                                            . " "
                                                            . $ogrenci["soyad"]
                                                        );

                                                        ?>

                                                    </strong>


                                                    <span>
                                                        Öğrenci
                                                    </span>


                                                </div>


                                            </div>


                                        </td>


                                        <!-- ÖĞRENCİ NO -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $ogrenci["ogrenciNo"]
                                            );

                                            ?>

                                        </td>


                                        <!-- BÖLÜM -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $ogrenci["bolum"]
                                            );

                                            ?>

                                        </td>


                                        <!-- VİZE -->

                                        <td>


                                            <?php if ($vize !== null): ?>


                                                <span class="grade">

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $vize
                                                    );

                                                    ?>

                                                </span>


                                            <?php else: ?>


                                                <span class="grade-empty">

                                                    Girilmedi

                                                </span>


                                            <?php endif; ?>


                                        </td>


                                        <!-- FİNAL -->

                                        <td>


                                            <?php if ($final !== null): ?>


                                                <span class="grade">

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $final
                                                    );

                                                    ?>

                                                </span>


                                            <?php else: ?>


                                                <span class="grade-empty">

                                                    Girilmedi

                                                </span>


                                            <?php endif; ?>


                                        </td>


                                        <!-- ORTALAMA -->
<!-- HARF NOTU -->

<td>

    <?php if ($ortalama !== null): ?>

        <span class="letter-grade">

            <?php
            echo htmlspecialchars($harfNotu);
            ?>

        </span>

    <?php else: ?>

        <span class="grade-empty">
            -
        </span>

    <?php endif; ?>

</td>


<!-- DURUM -->

<td>

    <?php if ($ortalama !== null): ?>

        <?php if ($durum === "Geçti"): ?>

            <span class="status status-success">
                ✓ Geçti
            </span>

        <?php else: ?>

            <span class="status status-danger">
                ✕ Kaldı
            </span>

        <?php endif; ?>

    <?php else: ?>

        <span class="status status-waiting">
            Not Bekleniyor
        </span>

    <?php endif; ?>

</td>
                                        <td>


                                            <?php if ($ortalama !== null): ?>


                                                <span class="average">

                                                    <?php

                                                    echo number_format(
                                                        $ortalama,
                                                        2,
                                                        ",",
                                                        "."
                                                    );

                                                    ?>

                                                </span>


                                            <?php else: ?>


                                                <span class="grade-empty">
                                                    -
                                                </span>


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>


                    </div>


                <?php else: ?>


                    <div class="empty">

                        Bu derse henüz öğrenci
                        kaydedilmemiş.

                    </div>


                <?php endif; ?>


            </div>


        <?php elseif ($secilenDersKodu !== ""): ?>


            <div class="card empty">

                Bu ders bulunamadı veya bu dersi
                görüntüleme yetkiniz bulunmuyor.

            </div>


        <?php endif; ?>


    </main>


</div>


</body>

</html>