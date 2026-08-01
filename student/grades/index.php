<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "ogrenci") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$ogrenciNo = $_SESSION["kullanici"] ?? "";
$ogrenciAdSoyad = $_SESSION["ad_soyad"] ?? "Öğrenci";


function notHesapla($vize, $final)
{
    $ortalama = round(($vize * 0.40) + ($final * 0.60), 2);

    if ($final < 50) {
        return [
            "ortalama" => $ortalama,
            "harf" => "FF",
            "durum" => "Kaldı"
        ];
    }

    if ($ortalama >= 90) {
        $harf = "AA";
    } elseif ($ortalama >= 85) {
        $harf = "BA";
    } elseif ($ortalama >= 80) {
        $harf = "BB";
    } elseif ($ortalama >= 75) {
        $harf = "CB";
    } elseif ($ortalama >= 70) {
        $harf = "CC";
    } elseif ($ortalama >= 65) {
        $harf = "DC";
    } elseif ($ortalama >= 60) {
        $harf = "DD";
    } elseif ($ortalama >= 50) {
        $harf = "FD";
    } else {
        $harf = "FF";
    }

    $durum = in_array(
        $harf,
        ["AA", "BA", "BB", "CB", "CC", "DC", "DD"],
        true
    ) ? "Geçti" : "Kaldı";

    return [
        "ortalama" => $ortalama,
        "harf" => $harf,
        "durum" => $durum
    ];
}


$sql = "
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.akademisyen,
        n.vize,
        n.final

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON od.dersKodu = d.dersKodu

    LEFT JOIN notlar n
        ON n.dersKodu = od.dersKodu
        AND n.ogrenciNo = od.ogrenciNo

    WHERE od.ogrenciNo = :ogrenciNo

    ORDER BY d.dersKodu ASC
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$notlar = $stmt->fetchAll(PDO::FETCH_ASSOC);


// İstatistikler
$toplamDers = count($notlar);
$notBekleyen = 0;
$gecen = 0;
$kalan = 0;

foreach ($notlar as $not) {

    if ($not["vize"] === null || $not["final"] === null) {
        $notBekleyen++;
        continue;
    }

    $sonuc = notHesapla(
        (float)$not["vize"],
        (float)$not["final"]
    );

    if ($sonuc["durum"] === "Geçti") {
        $gecen++;
    } else {
        $kalan++;
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notlarım</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f7fb;
    color: #1e293b;
    min-height: 100vh;
}

a {
    text-decoration: none;
}


/* SIDEBAR */

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
}

.logout-sidebar:hover {
    background: #b91c1c;
}


/* MAIN */

.main {
    margin-left: 250px;
    min-height: 100vh;
}


/* HEADER */

.header {
    height: 72px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 35px;
}

.header-title h2 {
    font-size: 18px;
    color: #0f172a;
}

.header-title p {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 3px;
}

.student-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.student-text {
    text-align: right;
}

.student-text strong {
    display: block;
    font-size: 13px;
    color: #0f172a;
}

.student-text span {
    font-size: 11px;
    color: #94a3b8;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dbeafe;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}


/* CONTENT */

.content {
    padding: 35px;
    max-width: 1500px;
    margin: auto;
}

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 27px;
    color: #0f172a;
    margin-bottom: 7px;
}

.page-header p {
    color: #64748b;
    font-size: 14px;
}


/* STATS */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px;
}

.stat-title {
    color: #64748b;
    font-size: 12px;
    margin-bottom: 7px;
}

.stat-number {
    font-size: 27px;
    font-weight: 700;
    color: #0f172a;
}


/* TABLE */

.table-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    padding: 20px 22px;
    border-bottom: 1px solid #e2e8f0;
}

.table-header h2 {
    font-size: 18px;
    color: #0f172a;
    margin-bottom: 5px;
}

.table-header p {
    color: #64748b;
    font-size: 13px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8fafc;
    color: #64748b;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    white-space: nowrap;
}

th,
td {
    padding: 14px;
    border-bottom: 1px solid #e2e8f0;
}

td {
    color: #334155;
    font-size: 13px;
}

tbody tr:hover {
    background: #f8fafc;
}

tbody tr:last-child td {
    border-bottom: none;
}


/* DERS */

.course-code {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
}

.course-name {
    font-weight: 600;
    color: #0f172a;
}


/* NOTLAR */

.grade {
    font-weight: 700;
    color: #0f172a;
}

.not-yok {
    color: #94a3b8;
    font-style: italic;
}

.pass {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.fail {
    display: inline-block;
    background: #fee2e2;
    color: #dc2626;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.waiting {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.empty {
    padding: 45px 25px;
    text-align: center;
    color: #64748b;
}


/* RESPONSIVE */

@media(max-width: 1000px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 800px) {

    .sidebar {
        width: 75px;
        padding: 20px 10px;
    }

    .logo {
        justify-content: center;
    }

    .logo-text,
    .sidebar-menu span:not(.menu-icon),
    .menu-title {
        display: none;
    }

    .sidebar-menu a {
        justify-content: center;
    }

    .sidebar-bottom {
        left: 10px;
        right: 10px;
    }

    .logout-sidebar {
        font-size: 0;
    }

    .logout-sidebar::after {
        content: "↪";
        font-size: 20px;
    }

    .main {
        margin-left: 75px;
    }
}

@media(max-width: 600px) {

    .content {
        padding: 22px 15px;
    }

    .header {
        padding: 0 18px;
    }

    .student-text {
        display: none;
    }

    .stats {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>


<?php
require_once __DIR__ . '/../../includes/sidebar.php';
?>


<div class="main">


    <header class="header">

        <div class="header-title">

            <h2>Notlarım</h2>

            <p>
                Üniversite Öğrenci Takip Sistemi
            </p>

        </div>


        <div class="student-profile">

            <div class="student-text">

                <strong>
                    <?php echo htmlspecialchars($ogrenciAdSoyad); ?>
                </strong>

                <span>
                    <?php echo htmlspecialchars($ogrenciNo); ?>
                </span>

            </div>


            <div class="avatar">

                <?php
                echo htmlspecialchars(
                    mb_strtoupper(
                        mb_substr(
                            $ogrenciAdSoyad,
                            0,
                            1,
                            "UTF-8"
                        ),
                        "UTF-8"
                    )
                );
                ?>

            </div>

        </div>

    </header>


    <main class="content">


        <div class="page-header">

            <h1>Notlarım</h1>

            <p>
                Derslerinize ait vize, final, ortalama,
                harf notu ve başarı durumunuzu görüntüleyebilirsiniz.
            </p>

        </div>


        <div class="stats">

            <div class="stat-card">

                <div class="stat-title">
                    Toplam Ders
                </div>

                <div class="stat-number">
                    <?php echo $toplamDers; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Not Bekleyen
                </div>

                <div class="stat-number">
                    <?php echo $notBekleyen; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Geçilen Ders
                </div>

                <div class="stat-number">
                    <?php echo $gecen; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Kalınan Ders
                </div>

                <div class="stat-number">
                    <?php echo $kalan; ?>
                </div>

            </div>

        </div>


        <div class="table-card">


            <div class="table-header">

                <h2>Ders Notları</h2>

                <p>
                    Vize %40 + Final %60 — Final barajı 50
                </p>

            </div>


            <?php if (count($notlar) > 0): ?>


                <div class="table-wrapper">

                    <table>

                        <thead>

                        <tr>
                            <th>Ders Kodu</th>
                            <th>Ders Adı</th>
                            <th>Akademisyen</th>
                            <th>Vize</th>
                            <th>Final</th>
                            <th>Ortalama</th>
                            <th>Harf</th>
                            <th>Durum</th>
                        </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($notlar as $not): ?>


                            <?php

                            if (
                                $not["vize"] !== null &&
                                $not["final"] !== null
                            ) {

                                $sonuc = notHesapla(
                                    (float)$not["vize"],
                                    (float)$not["final"]
                                );

                            } else {

                                $sonuc = null;
                            }

                            ?>


                            <tr>


                                <td>

                                    <span class="course-code">

                                        <?php
                                        echo htmlspecialchars(
                                            $not["dersKodu"]
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="course-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $not["dersAdi"]
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php

                                    echo !empty($not["akademisyen"])
                                        ? htmlspecialchars($not["akademisyen"])
                                        : "Atanmadı";

                                    ?>

                                </td>


                                <td>

                                    <?php if ($not["vize"] !== null): ?>

                                        <span class="grade">
                                            <?php echo htmlspecialchars($not["vize"]); ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="not-yok">
                                            Girilmedi
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($not["final"] !== null): ?>

                                        <span class="grade">
                                            <?php echo htmlspecialchars($not["final"]); ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="not-yok">
                                            Girilmedi
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($sonuc !== null): ?>

                                        <span class="grade">

                                            <?php
                                            echo number_format(
                                                $sonuc["ortalama"],
                                                2,
                                                ",",
                                                "."
                                            );
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="not-yok">-</span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($sonuc !== null): ?>

                                        <span class="grade">

                                            <?php
                                            echo htmlspecialchars(
                                                $sonuc["harf"]
                                            );
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="not-yok">-</span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($sonuc === null): ?>

                                        <span class="waiting">
                                            Not Bekliyor
                                        </span>

                                    <?php elseif ($sonuc["durum"] === "Geçti"): ?>

                                        <span class="pass">
                                            ✓ Geçti
                                        </span>

                                    <?php else: ?>

                                        <span class="fail">
                                            ✕ Kaldı
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

                    Henüz kayıtlı olduğunuz bir ders bulunmamaktadır.

                </div>


            <?php endif; ?>


        </div>


    </main>

</div>

</body>
</html>