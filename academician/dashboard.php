<?php

// ==========================================
// AKADEMİSYEN DASHBOARD
// student_project/academician/dashboard.php
// ==========================================


// ==========================================
// YETKİ KONTROLÜ
// ==========================================

require_once __DIR__ . '/../includes/auth.php';

rolKontrol("akademisyen");


// ==========================================
// VERİTABANI
// ==========================================

require_once __DIR__ . '/../database.php';


// ==========================================
// AKADEMİSYEN BİLGİLERİ
// ==========================================

$akademisyen = $_SESSION["kullanici"] ?? "";
$adSoyad = $_SESSION["ad_soyad"] ?? "Akademisyen";


// ==========================================
// 1 - DERS SAYISI
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)
    FROM ders
    WHERE akademisyen = :akademisyen
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$dersSayisi = (int)$stmt->fetchColumn();


// ==========================================
// 2 - BENZERSİZ ÖĞRENCİ SAYISI
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(DISTINCT od.ogrenciNo)

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON d.dersKodu = od.dersKodu

    WHERE d.akademisyen = :akademisyen
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$ogrenciSayisi = (int)$stmt->fetchColumn();


// ==========================================
// 3 - NOT BEKLEYEN KAYIT SAYISI
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON d.dersKodu = od.dersKodu

    LEFT JOIN notlar n
        ON n.ogrenciNo = od.ogrenciNo
        AND n.dersKodu = od.dersKodu

    WHERE d.akademisyen = :akademisyen

    AND (
        n.ogrenciNo IS NULL
        OR n.vize IS NULL
        OR n.final IS NULL
    )
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$notBekleyen = (int)$stmt->fetchColumn();


// ==========================================
// 4 - ADMIN'DEN GELEN DUYURU SAYISI
//
// ŞİMDİLİK:
// hedefKitle sütunu mevcut olmadığı için
// admin tarafından yayınlanan duyuruları alıyoruz.
// ==========================================

$stmt = $baglanti->prepare("
    SELECT COUNT(*)

    FROM duyuru

    WHERE yayinlayanRol = 'admin'
");

$stmt->execute();

$duyuruSayisi = (int)$stmt->fetchColumn();


// ==========================================
// 5 - SON 5 ADMIN DUYURUSU
// ==========================================

$stmt = $baglanti->prepare("
    SELECT
        duyuruId,
        baslik,
        icerik,
        duyuruTuru,
        yayinlayan,
        olusturmaTarihi

    FROM duyuru

    WHERE yayinlayanRol = 'admin'

    ORDER BY olusturmaTarihi DESC

    LIMIT 5
");

$stmt->execute();

$sonDuyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Akademisyen Paneli</title>

    <link
        rel="stylesheet"
        href="/student_project/assets/css/style.css"
    >

    <style>

        /* =====================================
           DASHBOARD
        ===================================== */

        .welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;
        }


        .welcome h1 {
            margin-bottom: 7px;

            color: #0f172a;

            font-size: 27px;
        }


        .welcome p {
            color: #64748b;

            font-size: 14px;

            line-height: 1.6;
        }


        /* =====================================
           İSTATİSTİKLER
        ===================================== */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 25px;
        }


        .stat-card {
            display: flex;

            align-items: center;

            gap: 16px;

            padding: 20px;

            background: white;

            border: 1px solid #e2e8f0;

            border-radius: 12px;

            transition: .2s;
        }


        .stat-card:hover {
            transform: translateY(-2px);

            box-shadow:
                0 6px 18px
                rgba(15, 23, 42, .06);
        }


        .stat-icon {
            width: 50px;
            height: 50px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            font-size: 22px;
        }


        .blue {
            background: #dbeafe;
        }


        .green {
            background: #dcfce7;
        }


        .orange {
            background: #ffedd5;
        }


        .purple {
            background: #f3e8ff;
        }


        .stat-info span {
            display: block;

            margin-bottom: 4px;

            color: #64748b;

            font-size: 12px;
        }


        .stat-info strong {
            color: #0f172a;

            font-size: 25px;
        }


        /* =====================================
           ANA GRID
        ===================================== */

        .dashboard-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 2fr)
                minmax(280px, 1fr);

            gap: 20px;
        }


        /* =====================================
           DASHBOARD KART
        ===================================== */

        .dashboard-card {
            overflow: hidden;

            background: white;

            border: 1px solid #e2e8f0;

            border-radius: 12px;
        }


        .dashboard-card-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 19px 21px;

            border-bottom:
                1px solid #e2e8f0;
        }


        .dashboard-card-header h2 {
            color: #0f172a;

            font-size: 17px;
        }


        .dashboard-card-header a {
            color: #2563eb;

            font-size: 12px;

            font-weight: 600;
        }


        .dashboard-card-header a:hover {
            text-decoration: underline;
        }


        /* =====================================
           DUYURULAR
        ===================================== */

        .announcement-list {
            padding: 0 20px;
        }


        .announcement {
            padding: 18px 0;

            border-bottom:
                1px solid #e2e8f0;
        }


        .announcement:last-child {
            border-bottom: none;
        }


        .announcement-top {
            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 8px;

            margin-bottom: 7px;
        }


        .announcement-type {
            display: inline-block;

            padding: 4px 8px;

            border-radius: 20px;

            background: #eff6ff;

            color: #1d4ed8;

            font-size: 10px;

            font-weight: 700;
        }


        .announcement-date {
            color: #94a3b8;

            font-size: 11px;
        }


        .announcement-publisher {
            color: #64748b;

            font-size: 11px;
        }


        .announcement h3 {
            margin-bottom: 6px;

            color: #0f172a;

            font-size: 14px;
        }


        .announcement p {
            display: -webkit-box;

            overflow: hidden;

            color: #64748b;

            font-size: 12px;

            line-height: 1.6;

            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }


        .empty {
            padding: 35px 20px;

            color: #64748b;

            text-align: center;

            font-size: 13px;
        }


        /* =====================================
           HIZLI İŞLEMLER
        ===================================== */

        .quick-actions {
            padding: 15px;
        }


        .quick-action {
            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px;

            margin-bottom: 8px;

            border-radius: 8px;

            color: #334155;

            transition: .2s;
        }


        .quick-action:last-child {
            margin-bottom: 0;
        }


        .quick-action:hover {
            background: #f8fafc;
        }


        .quick-icon {
            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 8px;

            background: #eff6ff;

            font-size: 17px;
        }


        .quick-text strong {
            display: block;

            margin-bottom: 3px;

            color: #0f172a;

            font-size: 13px;
        }


        .quick-text span {
            color: #94a3b8;

            font-size: 11px;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media(max-width: 1150px) {

            .stats {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media(max-width: 900px) {

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

        }


        @media(max-width: 600px) {

            .stats {
                grid-template-columns: 1fr;
            }


            .welcome h1 {
                font-size: 22px;
            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     ORTAK SIDEBAR
=========================================== -->

<?php
require_once __DIR__ . '/../includes/sidebar.php';
?>


<div class="main">


    <!-- ======================================
         ORTAK HEADER
    ======================================= -->

    <?php
    require_once __DIR__ . '/../includes/header.php';
    ?>


    <main class="content">


        <!-- ==================================
             HOŞ GELDİN
        =================================== -->

        <div class="welcome">

            <div>

                <h1>

                    Hoş Geldiniz,
                    <?php
                    echo htmlspecialchars($adSoyad);
                    ?>

                </h1>


                <p>

                    Derslerinizi, öğrencilerinizi,
                    not işlemlerini ve duyurularınızı
                    buradan yönetebilirsiniz.

                </p>

            </div>

        </div>


        <!-- ==================================
             İSTATİSTİKLER
        =================================== -->

        <div class="stats">


            <!-- DERS -->

            <div class="stat-card">

                <div class="stat-icon blue">
                    📚
                </div>

                <div class="stat-info">

                    <span>
                        Derslerim
                    </span>

                    <strong>
                        <?php echo $dersSayisi; ?>
                    </strong>

                </div>

            </div>


            <!-- ÖĞRENCİ -->

            <div class="stat-card">

                <div class="stat-icon green">
                    👥
                </div>

                <div class="stat-info">

                    <span>
                        Öğrencilerim
                    </span>

                    <strong>
                        <?php echo $ogrenciSayisi; ?>
                    </strong>

                </div>

            </div>


            <!-- NOT -->

            <div class="stat-card">

                <div class="stat-icon orange">
                    📝
                </div>

                <div class="stat-info">

                    <span>
                        Not Bekleyen
                    </span>

                    <strong>
                        <?php echo $notBekleyen; ?>
                    </strong>

                </div>

            </div>


            <!-- DUYURU -->

            <div class="stat-card">

                <div class="stat-icon purple">
                    📢
                </div>

                <div class="stat-info">

                    <span>
                        Gelen Duyuru
                    </span>

                    <strong>
                        <?php echo $duyuruSayisi; ?>
                    </strong>

                </div>

            </div>


        </div>


        <!-- ==================================
             ALT ALAN
        =================================== -->

        <div class="dashboard-grid">


            <!-- ==============================
                 SON DUYURULAR
            =============================== -->

            <section class="dashboard-card">


                <div class="dashboard-card-header">

                    <h2>
                        📢 Son Duyurular
                    </h2>


                    <a
                        href="/student_project/academician/announcements/index.php?tab=gelen"
                    >
                        Tümünü Gör →
                    </a>

                </div>


                <?php if (count($sonDuyurular) > 0): ?>


                    <div class="announcement-list">


                        <?php foreach ($sonDuyurular as $duyuru): ?>


                            <div class="announcement">


                                <div class="announcement-top">


                                    <span class="announcement-type">

                                        <?php

                                        echo htmlspecialchars(
                                            ucfirst(
                                                $duyuru["duyuruTuru"]
                                                ?? "Genel"
                                            )
                                        );

                                        ?>

                                    </span>


                                    <span class="announcement-date">

                                        <?php

                                        if (
                                            !empty(
                                                $duyuru["olusturmaTarihi"]
                                            )
                                        ) {

                                            echo date(
                                                "d.m.Y H:i",
                                                strtotime(
                                                    $duyuru["olusturmaTarihi"]
                                                )
                                            );

                                        }

                                        ?>

                                    </span>


                                    <?php if (!empty($duyuru["yayinlayan"])): ?>

                                        <span class="announcement-publisher">

                                            •
                                            <?php
                                            echo htmlspecialchars(
                                                $duyuru["yayinlayan"]
                                            );
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                </div>


                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $duyuru["baslik"]
                                    );
                                    ?>

                                </h3>


                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $duyuru["icerik"]
                                    );
                                    ?>

                                </p>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div class="empty">

                        Henüz admin tarafından
                        yayınlanmış bir duyuru bulunmamaktadır.

                    </div>


                <?php endif; ?>


            </section>


            <!-- ==============================
                 HIZLI İŞLEMLER
            =============================== -->

            <section class="dashboard-card">


                <div class="dashboard-card-header">

                    <h2>
                        Hızlı İşlemler
                    </h2>

                </div>


                <div class="quick-actions">


                    <!-- DERSLER -->

                    <a
                        href="/student_project/academician/courses/index.php"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            📚
                        </div>


                        <div class="quick-text">

                            <strong>
                                Derslerimi Gör
                            </strong>

                            <span>
                                Verdiğiniz dersleri görüntüleyin
                            </span>

                        </div>

                    </a>


                    <!-- ÖĞRENCİLER -->

                    <a
                        href="/student_project/academician/students/index.php"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            👥
                        </div>


                        <div class="quick-text">

                            <strong>
                                Öğrencilerim
                            </strong>

                            <span>
                                Derslerinize kayıtlı öğrenciler
                            </span>

                        </div>

                    </a>


                    <!-- NOTLAR -->

                    <a
                        href="/student_project/academician/grades/index.php"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            📝
                        </div>


                        <div class="quick-text">

                            <strong>
                                Not İşlemleri
                            </strong>

                            <span>
                                Vize ve final notlarını yönetin
                            </span>

                        </div>

                    </a>


                    <!-- DUYURU -->

                    <a
                        href="/student_project/academician/announcements/add.php"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            📢
                        </div>


                        <div class="quick-text">

                            <strong>
                                Duyuru Yayınla
                            </strong>

                            <span>
                                Öğrencilerinize duyuru gönderin
                            </span>

                        </div>

                    </a>


                    <!-- ŞİFRE -->

                    <a
                        href="/student_project/academician/password/index.php"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            🔐
                        </div>


                        <div class="quick-text">

                            <strong>
                                Şifre Değiştir
                            </strong>

                            <span>
                                Hesap şifrenizi güncelleyin
                            </span>

                        </div>

                    </a>


                </div>


            </section>


        </div>


    </main>


</div>


</body>

</html>