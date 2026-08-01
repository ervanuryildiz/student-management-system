<?php

session_start();

// Sadece öğrenci erişebilir
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "ogrenci") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


// ==========================================
// ÖĞRENCİ BİLGİLERİ
// ==========================================

$ogrenciNo = $_SESSION["kullanici"] ?? "";
$ogrenciAdSoyad = $_SESSION["ad_soyad"] ?? "Öğrenci";


// ==========================================
// ÖĞRENCİNİN DERSLERİ
// ==========================================

$sql = "
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.bolum,
        d.akademisyen

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON od.dersKodu = d.dersKodu

    WHERE od.ogrenciNo = :ogrenciNo

    ORDER BY d.dersKodu ASC
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dersSayisi = count($dersler);

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Derslerim</title>

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
   MAIN
========================================== */

.main {
    margin-left: 250px;
    min-height: 100vh;
}


/* ==========================================
   HEADER
========================================== */

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


/* ==========================================
   CONTENT
========================================== */

.content {
    padding: 35px;

    max-width: 1500px;

    margin: auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;

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


/* ==========================================
   İSTATİSTİK
========================================== */

.stat-card {
    min-width: 150px;

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    padding: 14px 18px;
}

.stat-title {
    font-size: 11px;
    color: #64748b;

    margin-bottom: 5px;
}

.stat-number {
    font-size: 24px;

    color: #0f172a;

    font-weight: 700;
}


/* ==========================================
   TABLE CARD
========================================== */

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
    font-size: 13px;

    color: #64748b;
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

    font-size: 11px;

    text-transform: uppercase;

    font-weight: 600;

    text-align: left;
}

th,
td {
    padding: 14px 18px;

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


/* ==========================================
   DERS KODU
========================================== */

.course-code {
    display: inline-block;

    background: #eff6ff;

    color: #1d4ed8;

    padding: 6px 9px;

    border-radius: 6px;

    font-size: 11px;

    font-weight: 700;
}

.course-name {
    color: #0f172a;

    font-weight: 600;
}


/* ==========================================
   AKADEMİSYEN
========================================== */

.academic {
    display: flex;

    align-items: center;

    gap: 9px;
}

.academic-avatar {
    width: 32px;
    height: 32px;

    border-radius: 50%;

    background: #f1f5f9;

    color: #475569;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 12px;

    font-weight: 700;
}

.no-academic {
    color: #94a3b8;

    font-style: italic;
}


/* ==========================================
   EMPTY
========================================== */

.empty {
    padding: 50px 25px;

    text-align: center;

    color: #64748b;
}

.empty-icon {
    font-size: 35px;

    margin-bottom: 12px;
}

.empty h3 {
    color: #334155;

    margin-bottom: 7px;

    font-size: 16px;
}

.empty p {
    font-size: 13px;
}


/* ==========================================
   RESPONSIVE
========================================== */

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

    .page-header {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .stat-card {
        width: 100%;
    }
}

</style>

</head>


<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<?php
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<!-- ==========================================
     MAIN
========================================== -->

<div class="main">


    <!-- HEADER -->

    <header class="header">


        <div class="header-title">

            <h2>
                Derslerim
            </h2>

            <p>
                Üniversite Öğrenci Takip Sistemi
            </p>

        </div>


        <div class="student-profile">


            <div class="student-text">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $ogrenciAdSoyad
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


    <!-- ==========================================
         CONTENT
    ========================================== -->

    <main class="content">


        <div class="page-header">


            <div>

                <h1>
                    Derslerim
                </h1>

                <p>
                    Kayıtlı olduğunuz dersleri ve ders
                    bilgilerini görüntüleyebilirsiniz.
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Kayıtlı Ders
                </div>

                <div class="stat-number">

                    <?php
                    echo $dersSayisi;
                    ?>

                </div>

            </div>


        </div>


        <!-- ==========================================
             DERS TABLOSU
        ========================================== -->

        <div class="table-card">


            <div class="table-header">

                <h2>
                    Kayıtlı Dersler
                </h2>

                <p>
                    Bu dönem kayıtlı olduğunuz dersler.
                </p>

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
                                Akademisyen
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


                                <!-- AKADEMİSYEN -->

                                <td>


                                    <?php if (!empty($ders["akademisyen"])): ?>


                                        <div class="academic">


                                            <div class="academic-avatar">

                                                <?php

                                                echo htmlspecialchars(
                                                    mb_strtoupper(
                                                        mb_substr(
                                                            $ders["akademisyen"],
                                                            0,
                                                            1,
                                                            "UTF-8"
                                                        ),
                                                        "UTF-8"
                                                    )
                                                );

                                                ?>

                                            </div>


                                            <span>

                                                <?php
                                                echo htmlspecialchars(
                                                    $ders["akademisyen"]
                                                );
                                                ?>

                                            </span>


                                        </div>


                                    <?php else: ?>


                                        <span class="no-academic">
                                            Akademisyen atanmadı
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


                    <div class="empty-icon">
                        📚
                    </div>


                    <h3>
                        Kayıtlı ders bulunamadı
                    </h3>


                    <p>
                        Henüz kayıtlı olduğunuz bir ders bulunmamaktadır.
                    </p>


                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>