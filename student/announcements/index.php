<?php

session_start();


// ==========================================
// YETKİ KONTROLÜ
// ==========================================

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "ogrenci"
) {
    header("Location: /student_project/login.php");
    exit;
}


// ==========================================
// VERİTABANI
// ==========================================

require_once __DIR__ . '/../../database.php';


$ogrenciNo = $_SESSION["kullanici"] ?? "";
$adSoyad   = $_SESSION["ad_soyad"] ?? "Öğrenci";


// ==========================================
// ÖĞRENCİNİN DUYURULARINI GETİR
//
// - Öğrencilere gönderilmiş olmalı
// - Genel duyuruları görür
// - Ders duyurusunu yalnızca o dersi alıyorsa görür
// ==========================================

$sql = "
    SELECT DISTINCT

        d.duyuruId,
        d.baslik,
        d.icerik,
        d.duyuruTuru,
        d.dersKodu,
        d.yayinlayan,
        d.yayinlayanRol,
        d.olusturmaTarihi,
        d.guncellemeTarihi

    FROM duyuru d

    WHERE d.hedefKitle = 'ogrenci'

    AND
    (
        d.dersKodu IS NULL

        OR d.dersKodu = ''

        OR EXISTS
        (
            SELECT 1

            FROM ogrenci_ders od

            WHERE od.ogrenciNo = :ogrenciNo

            AND od.dersKodu = d.dersKodu
        )
    )

    ORDER BY d.olusturmaTarihi DESC
";


$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// AVATAR HARFİ
// ==========================================

$avatarHarf = mb_strtoupper(
    mb_substr(
        $adSoyad,
        0,
        1,
        "UTF-8"
    ),
    "UTF-8"
);

?>

<!DOCTYPE html>

<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Duyurular</title>


<style>

/* ==========================================
   GENEL
========================================== */

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

a {
    text-decoration: none;
}


/* ==========================================
   SIDEBAR
   Derslerim ekranıyla aynı
========================================== */

/* ==========================================
   SIDEBAR
========================================== */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #0f172a;
    color: white;

    padding: 25px 18px;

    z-index: 1000;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 5px 8px 25px;

    border-bottom: 1px solid #263247;

    margin-bottom: 25px;
}

.logo-icon {
    width: 45px;
    height: 45px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #2563eb;

    border-radius: 10px;

    font-size: 22px;
}

.logo h2 {
    font-size: 16px;
    margin-bottom: 3px;
}

.logo span {
    font-size: 11px;
    color: #94a3b8;
}

.menu-title {
    font-size: 11px;

    color: #64748b;

    text-transform: uppercase;

    margin: 20px 10px 10px;

    letter-spacing: 1px;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 13px;

    border-radius: 8px;

    color: #cbd5e1;

    font-size: 14px;

    transition: .2s;
}

.sidebar-menu a:hover {
    background: #1e293b;
    color: white;
}

.sidebar-menu a.active {
    background: #2563eb;
    color: white;
}

.menu-icon {
    width: 22px;
    text-align: center;
}

.sidebar-bottom {
    position: absolute;

    bottom: 25px;

    left: 18px;
    right: 18px;
}

.logout-sidebar {
    display: block;

    padding: 11px;

    text-align: center;

    background: #dc2626;

    color: white;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;

    transition: .2s;
}

.logout-sidebar:hover {
    background: #b91c1c;
}


/* ==========================================
   ANA ALAN
========================================== */

.main {

    margin-left: 270px;

    min-height: 100vh;
}


/* ==========================================
   HEADER
========================================== */

.header {

    height: 76px;

    background: white;

    border-bottom: 1px solid #e2e8f0;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 38px;
}


.header-left h2 {

    margin: 0 0 4px;

    color: #0f172a;

    font-size: 17px;
}


.header-left p {

    margin: 0;

    color: #94a3b8;

    font-size: 11px;
}


.profile {

    display: flex;

    align-items: center;

    gap: 12px;
}


.profile-info {

    text-align: right;
}


.profile-info strong {

    display: block;

    color: #0f172a;

    font-size: 12px;

    margin-bottom: 3px;
}


.profile-info span {

    color: #94a3b8;

    font-size: 10px;
}


.avatar {

    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #dbeafe;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;
}


/* ==========================================
   CONTENT
========================================== */

.content {

    padding: 38px;

    max-width: 1450px;

    margin: auto;
}


/* ==========================================
   SAYFA ÜSTÜ
========================================== */

.page-top {

    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    gap: 25px;

    margin-bottom: 27px;
}


.page-title h1 {

    margin: 0 0 7px;

    color: #0f172a;

    font-size: 27px;

    font-weight: 700;
}


.page-title p {

    margin: 0;

    color: #64748b;

    font-size: 13px;
}


/* ==========================================
   DUYURU SAYISI
========================================== */

.count-box {

    min-width: 145px;

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    padding: 16px 20px;
}


.count-box span {

    display: block;

    color: #64748b;

    font-size: 10px;

    margin-bottom: 7px;
}


.count-box strong {

    color: #0f172a;

    font-size: 22px;
}


/* ==========================================
   DUYURU PANELİ
========================================== */

.panel {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    overflow: hidden;
}


.panel-header {

    padding: 21px 23px;

    border-bottom: 1px solid #e2e8f0;
}


.panel-header h2 {

    margin: 0 0 5px;

    color: #0f172a;

    font-size: 17px;
}


.panel-header p {

    margin: 0;

    color: #64748b;

    font-size: 11px;
}


/* ==========================================
   DUYURU SATIRI
========================================== */

.announcement {

    padding: 20px 23px;

    border-bottom: 1px solid #e8edf3;

    transition: .15s;
}


.announcement:last-child {

    border-bottom: none;
}


.announcement:hover {

    background: #f8fafc;
}


.announcement-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 10px;
}


.announcement-title-area {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 9px;
}


.announcement-title {

    color: #0f172a;

    font-size: 15px;

    font-weight: 650;
}


.announcement-date {

    color: #94a3b8;

    font-size: 10px;

    white-space: nowrap;
}


/* ==========================================
   DUYURU ETİKETLERİ
========================================== */

.badge {

    display: inline-block;

    padding: 4px 8px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: 700;

    text-transform: uppercase;
}


.badge-genel {

    background: #eff6ff;

    color: #2563eb;
}


.badge-ders {

    background: #f3e8ff;

    color: #7e22ce;
}


.badge-sinav {

    background: #fff7ed;

    color: #c2410c;
}


.badge-bilgilendirme {

    background: #ecfdf5;

    color: #15803d;
}


/* ==========================================
   DUYURU İÇERİĞİ
========================================== */

.announcement-content {

    color: #475569;

    font-size: 13px;

    line-height: 1.65;

    margin-bottom: 13px;
}


/* ==========================================
   ALT BİLGİ
========================================== */

.announcement-meta {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 8px;

    color: #94a3b8;

    font-size: 10px;
}


.publisher {

    color: #64748b;
}


.course-code {

    color: #2563eb;

    font-weight: 600;
}


.separator {

    color: #cbd5e1;
}


/* ==========================================
   BOŞ DURUM
========================================== */

.empty {

    padding: 55px 25px;

    text-align: center;

    color: #64748b;
}


.empty-icon {

    font-size: 30px;

    margin-bottom: 10px;
}


.empty h3 {

    margin: 0 0 6px;

    color: #0f172a;

    font-size: 15px;
}


.empty p {

    margin: 0;

    font-size: 11px;
}


/* ==========================================
   RESPONSIVE
========================================== */

@media(max-width: 800px) {

    .sidebar {

        width: 75px;

        padding:
            25px
            10px;
    }


    .logo {

        justify-content: center;

        padding-left: 0;
        padding-right: 0;
    }


    .logo-text,
    .menu-title,
    .sidebar-menu span:not(.menu-icon) {

        display: none;
    }


    .sidebar-menu a {

        justify-content: center;
    }


    .main {
    margin-left: 250px;
    min-height: 100vh;
}


    .page-top {

        flex-direction: column;

        align-items: stretch;
    }


    .count-box {

        width: 100%;
    }

}


@media(max-width: 550px) {

    .content {

        padding: 25px 17px;
    }


    .header {

        padding: 0 18px;
    }


    .profile-info {

        display: none;
    }


    .announcement-top {

        flex-direction: column;

        gap: 6px;
    }

}

</style>

</head>


<body>


<!-- ==========================================
     ORTAK SIDEBAR
========================================== -->

<?php

require_once __DIR__ . '/../../includes/sidebar.php';

?>


<!-- ==========================================
     ANA ALAN
========================================== -->

<div class="main">


    <!-- ======================================
         HEADER
         Derslerim ekranıyla aynı yapı
    ======================================= -->

    <header class="header">


        <div class="header-left">

            <h2>
                Duyurular
            </h2>

            <p>
                Üniversite Öğrenci Takip Sistemi
            </p>

        </div>


        <div class="profile">


            <div class="profile-info">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $adSoyad
                    );
                    ?>

                </strong>


                <span>

                    <?php
                    echo htmlspecialchars(
                        $ogrenciNo
                    );
                    ?>

                </span>

            </div>


            <div class="avatar">

                <?php
                echo htmlspecialchars(
                    $avatarHarf
                );
                ?>

            </div>


        </div>


    </header>


    <!-- ======================================
         CONTENT
    ======================================= -->

    <main class="content">


        <!-- SAYFA ÜSTÜ -->

        <div class="page-top">


            <div class="page-title">

                <h1>
                    Duyurular
                </h1>

                <p>
                    Yönetim ve akademisyenler tarafından
                    yayınlanan duyuruları görüntüleyebilirsiniz.
                </p>

            </div>


            <div class="count-box">

                <span>
                    Toplam Duyuru
                </span>

                <strong>

                    <?php
                    echo count($duyurular);
                    ?>

                </strong>

            </div>


        </div>


        <!-- ==================================
             PANEL
        =================================== -->

        <div class="panel">


            <div class="panel-header">

                <h2>
                    Duyurular
                </h2>

                <p>
                    Size yönelik yayınlanan güncel duyurular.
                </p>

            </div>


            <?php if (count($duyurular) > 0): ?>


                <?php foreach ($duyurular as $duyuru): ?>


                    <?php

                    // ==================================
                    // DUYURU TÜRÜ
                    // ==================================

                    $tur = strtolower(
                        trim(
                            $duyuru["duyuruTuru"]
                            ?? "genel"
                        )
                    );


                    $izinliTurler = [
                        "genel",
                        "ders",
                        "sinav",
                        "bilgilendirme"
                    ];


                    if (
                        !in_array(
                            $tur,
                            $izinliTurler,
                            true
                        )
                    ) {

                        $tur = "genel";
                    }


                    // ==================================
                    // TÜR YAZISI
                    // ==================================

                    switch ($tur) {

                        case "ders":

                            $turYazisi = "Ders";

                            break;


                        case "sinav":

                            $turYazisi = "Sınav";

                            break;


                        case "bilgilendirme":

                            $turYazisi =
                                "Bilgilendirme";

                            break;


                        default:

                            $turYazisi = "Genel";

                            break;
                    }


                    // ==================================
                    // YAYINLAYAN
                    // ==================================

                    if (
                        ($duyuru["yayinlayanRol"] ?? "")
                        === "admin"
                    ) {

                        $yayinlayan =
                            "Üniversite Yönetimi";

                    } else {

                        $yayinlayan =
                            $duyuru["yayinlayan"]
                            ?? "Akademisyen";
                    }

                    ?>


                    <div class="announcement">


                        <!-- ÜST -->

                        <div class="announcement-top">


                            <div class="announcement-title-area">


                                <div class="announcement-title">

                                    <?php

                                    echo htmlspecialchars(
                                        $duyuru["baslik"]
                                    );

                                    ?>

                                </div>


                                <span
                                    class="badge badge-<?php echo $tur; ?>"
                                >

                                    <?php
                                    echo $turYazisi;
                                    ?>

                                </span>


                            </div>


                            <div class="announcement-date">

                                <?php

                                if (
                                    !empty(
                                        $duyuru[
                                            "olusturmaTarihi"
                                        ]
                                    )
                                ) {

                                    echo date(
                                        "d.m.Y H:i",
                                        strtotime(
                                            $duyuru[
                                                "olusturmaTarihi"
                                            ]
                                        )
                                    );
                                }

                                ?>

                            </div>


                        </div>


                        <!-- İÇERİK -->

                        <div class="announcement-content">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $duyuru["icerik"]
                                )
                            );

                            ?>

                        </div>


                        <!-- ALT BİLGİ -->

                        <div class="announcement-meta">


                            <span class="publisher">

                                <?php

                                echo htmlspecialchars(
                                    $yayinlayan
                                );

                                ?>

                            </span>


                            <span class="separator">
                                •
                            </span>


                            <?php if (
                                !empty(
                                    $duyuru["dersKodu"]
                                )
                            ): ?>


                                <span class="course-code">

                                    <?php

                                    echo htmlspecialchars(
                                        $duyuru[
                                            "dersKodu"
                                        ]
                                    );

                                    ?>

                                </span>


                            <?php else: ?>


                                <span>
                                    Genel Duyuru
                                </span>


                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $duyuru[
                                        "guncellemeTarihi"
                                    ]
                                )
                            ): ?>


                                <span class="separator">
                                    •
                                </span>


                                <span>
                                    Güncellendi
                                </span>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="empty">

                    <div class="empty-icon">
                        📭
                    </div>

                    <h3>
                        Henüz duyuru bulunmuyor
                    </h3>

                    <p>
                        Size yönelik yeni bir duyuru
                        yayınlandığında burada
                        görüntülenecektir.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>