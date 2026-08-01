<?php

session_start();

// ==========================================
// SADECE ADMIN ERİŞEBİLİR
// ==========================================
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

// ==========================================
// VERİTABANI BAĞLANTISI
// admin/dashboard.php -> ../database.php
// ==========================================
require_once __DIR__ . '/../database.php';


// ==========================================
// İSTATİSTİKLER
// ==========================================

// Öğrenci sayısı
$stmt = $baglanti->query("SELECT COUNT(*) FROM ogrenci");
$toplamOgrenci = (int)$stmt->fetchColumn();


// Akademisyen sayısı
$stmt = $baglanti->query("
    SELECT COUNT(*)
    FROM admin
    WHERE unvan = 'akademisyen'
");
$toplamAkademisyen = (int)$stmt->fetchColumn();


// Ders sayısı
$stmt = $baglanti->query("SELECT COUNT(*) FROM ders");
$toplamDers = (int)$stmt->fetchColumn();


// Bölüm sayısı
// ders tablosundaki bolum alanından hesaplanıyor.
$stmt = $baglanti->query("
    SELECT COUNT(DISTINCT bolum)
    FROM ders
    WHERE bolum IS NOT NULL
    AND bolum <> ''
");
$toplamBolum = (int)$stmt->fetchColumn();


// Admin bilgileri
$adminAdSoyad = $_SESSION["ad_soyad"] ?? "Admin";
$kullaniciAdi = $_SESSION["kullanici"] ?? "";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Paneli</title>

<style>

/* ==========================================
   GENEL
========================================== */

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


/* ==========================================
   SIDEBAR MENÜ
========================================== */

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

    transition: 0.2s;
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


/* ==========================================
   SIDEBAR ALT
========================================== */

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


/* ==========================================
   ANA SAYFA
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


.admin-profile {

    display: flex;

    align-items: center;

    gap: 12px;
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


.admin-text {

    text-align: right;
}


.admin-text strong {

    display: block;

    font-size: 13px;

    color: #0f172a;
}


.admin-text span {

    font-size: 11px;

    color: #94a3b8;
}


/* ==========================================
   CONTENT
========================================== */

.content {

    padding: 35px;

    max-width: 1450px;

    margin: auto;
}


/* ==========================================
   WELCOME
========================================== */

.welcome {

    margin-bottom: 28px;
}


.welcome h1 {

    font-size: 27px;

    color: #0f172a;

    margin-bottom: 7px;
}


.welcome p {

    color: #64748b;

    font-size: 14px;
}


/* ==========================================
   İSTATİSTİKLER
========================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 35px;
}


.stat-card {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 20px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    transition: 0.2s;
}


.stat-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(15,23,42,0.06);
}


.stat-title {

    color: #64748b;

    font-size: 12px;

    margin-bottom: 7px;
}


.stat-number {

    font-size: 28px;

    font-weight: 700;

    color: #0f172a;
}


.stat-icon {

    width: 48px;

    height: 48px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

    background: #eff6ff;
}


/* ==========================================
   SECTION
========================================== */

.section-header {

    margin-bottom: 17px;
}


.section-header h2 {

    font-size: 19px;

    color: #0f172a;

    margin-bottom: 5px;
}


.section-header p {

    color: #64748b;

    font-size: 13px;
}


/* ==========================================
   YÖNETİM KARTLARI
========================================== */

.management-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;
}


.management-card {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 24px;

    color: #1e293b;

    transition: 0.2s;

    position: relative;

    overflow: hidden;
}


.management-card:hover {

    transform: translateY(-3px);

    border-color: #bfdbfe;

    box-shadow:
        0 10px 25px rgba(15,23,42,0.07);
}


.card-top {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 15px;
}


.card-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eff6ff;

    border-radius: 10px;

    font-size: 22px;
}


.management-card h3 {

    color: #0f172a;

    font-size: 17px;
}


.management-card p {

    color: #64748b;

    font-size: 13px;

    line-height: 1.6;

    margin-bottom: 17px;
}


.card-link {

    color: #2563eb;

    font-size: 13px;

    font-weight: 600;
}


/* ==========================================
   ALT DURUM KARTI
========================================== */

.system-info {

    margin-top: 30px;

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 18px 22px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.system-text strong {

    display: block;

    color: #0f172a;

    font-size: 13px;

    margin-bottom: 4px;
}


.system-text span {

    color: #94a3b8;

    font-size: 12px;
}


.system-status {

    display: flex;

    align-items: center;

    gap: 7px;

    color: #15803d;

    font-size: 12px;

    font-weight: 600;
}


.status-dot {

    width: 8px;

    height: 8px;

    border-radius: 50%;

    background: #22c55e;
}


/* ==========================================
   RESPONSIVE
========================================== */

@media(max-width: 1000px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);
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


    .management-grid {

        grid-template-columns: 1fr;
    }

}


@media(max-width: 600px) {

    .content {

        padding: 22px 15px;
    }


    .header {

        padding: 0 18px;
    }


    .admin-text {

        display: none;
    }


    .stats {

        grid-template-columns: 1fr;
    }


    .system-info {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;
    }

}

</style>

</head>

<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<aside class="sidebar">


    <div class="logo">

        <div class="logo-icon">
            🎓
        </div>


        <div class="logo-text">

            <h2>
                Öğrenci Takip
            </h2>

            <span>
                Yönetim Sistemi
            </span>

        </div>

    </div>


    <div class="menu-title">
        Ana Menü
    </div>


    <nav class="sidebar-menu">


        <a
            href="/student_project/admin/dashboard.php"
            class="active"
        >

            <span class="menu-icon">▦</span>

            <span>
                Dashboard
            </span>

        </a>


        <a
            href="/student_project/admin/students/index.php"
        >

            <span class="menu-icon">🎓</span>

            <span>
                Öğrenciler
            </span>

        </a>


        <a
            href="/student_project/admin/academicians/index.php"
        >

            <span class="menu-icon">👨‍🏫</span>

            <span>
                Akademisyenler
            </span>

        </a>


        <a
            href="/student_project/admin/faculties/index.php"
        >

            <span class="menu-icon">🏫</span>

            <span>
                Fakülteler
            </span>

        </a>


        <a
            href="/student_project/admin/courses/index.php"
        >

            <span class="menu-icon">📚</span>

            <span>
                Dersler
            </span>

        </a>

        <a href="/student_project/admin/announcements/index.php">
    <span class="menu-icon">📢</span>
    <span>Duyurular</span>
</a>


    </nav>


    <div class="sidebar-bottom">

        <a
            href="/student_project/logout.php"
            class="logout-sidebar"
        >
            Çıkış Yap
        </a>

    </div>


</aside>



<!-- ==========================================
     ANA ALAN
========================================== -->

<div class="main">


    <!-- HEADER -->

    <header class="header">


        <div class="header-title">

            <h2>
                Admin Paneli
            </h2>

            <p>
                Üniversite Öğrenci Takip Sistemi
            </p>

        </div>


        <div class="admin-profile">


            <div class="admin-text">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $adminAdSoyad
                    );
                    ?>

                </strong>


                <span>

                    <?php
                    echo htmlspecialchars(
                        $kullaniciAdi
                    );
                    ?>

                </span>

            </div>


            <div class="avatar">

                <?php

                $ilkHarf =
                    mb_substr(
                        $adminAdSoyad,
                        0,
                        1,
                        "UTF-8"
                    );

                echo htmlspecialchars(
                    mb_strtoupper(
                        $ilkHarf,
                        "UTF-8"
                    )
                );

                ?>

            </div>


        </div>


    </header>



    <!-- ======================================
         CONTENT
    ======================================= -->

    <main class="content">


        <!-- WELCOME -->

        <section class="welcome">


            <h1>

                Hoş geldiniz,
                <?php
                echo htmlspecialchars(
                    $adminAdSoyad
                );
                ?>

            </h1>


            <p>

                Üniversite öğrenci takip sisteminin
                genel durumunu görüntüleyebilir ve
                yönetim işlemlerine erişebilirsiniz.

            </p>


        </section>



        <!-- ======================================
             İSTATİSTİKLER
        ======================================= -->

        <section class="stats">


            <div class="stat-card">

                <div>

                    <div class="stat-title">
                        Toplam Öğrenci
                    </div>

                    <div class="stat-number">

                        <?php
                        echo $toplamOgrenci;
                        ?>

                    </div>

                </div>


                <div class="stat-icon">
                    🎓
                </div>

            </div>



            <div class="stat-card">

                <div>

                    <div class="stat-title">
                        Toplam Akademisyen
                    </div>

                    <div class="stat-number">

                        <?php
                        echo $toplamAkademisyen;
                        ?>

                    </div>

                </div>


                <div class="stat-icon">
                    👨‍🏫
                </div>

            </div>



            <div class="stat-card">

                <div>

                    <div class="stat-title">
                        Toplam Ders
                    </div>

                    <div class="stat-number">

                        <?php
                        echo $toplamDers;
                        ?>

                    </div>

                </div>


                <div class="stat-icon">
                    📚
                </div>

            </div>



            <div class="stat-card">

                <div>

                    <div class="stat-title">
                        Toplam Bölüm
                    </div>

                    <div class="stat-number">

                        <?php
                        echo $toplamBolum;
                        ?>

                    </div>

                </div>


                <div class="stat-icon">
                    🏫
                </div>

            </div>


        </section>



        <!-- ======================================
             YÖNETİM İŞLEMLERİ
        ======================================= -->

        <section>


            <div class="section-header">

                <h2>
                    Yönetim İşlemleri
                </h2>

                <p>
                    İşlem yapmak istediğiniz modülü seçin.
                </p>

            </div>



            <div class="management-grid">


                <!-- ÖĞRENCİ -->

                <a
                    href="/student_project/admin/students/index.php"
                    class="management-card"
                >


                    <div class="card-top">

                        <div class="card-icon">
                            🎓
                        </div>

                        <h3>
                            Öğrenci İşlemleri
                        </h3>

                    </div>


                    <p>

                        Öğrencileri listeleyebilir,
                        yeni öğrenci ekleyebilir,
                        bilgilerini güncelleyebilir,
                        silebilir ve öğrencilerin
                        ders kayıtlarını yönetebilirsiniz.

                    </p>


                    <span class="card-link">
                        Öğrencileri Yönet →
                    </span>


                </a>



                <!-- AKADEMİSYEN -->

                <a
                    href="/student_project/admin/academicians/index.php"
                    class="management-card"
                >


                    <div class="card-top">

                        <div class="card-icon">
                            👨‍🏫
                        </div>

                        <h3>
                            Akademisyen İşlemleri
                        </h3>

                    </div>


                    <p>

                        Akademisyenleri görüntüleyebilir,
                        sisteme yeni akademisyen
                        ekleyebilir ve mevcut
                        akademisyen bilgilerini
                        güncelleyebilirsiniz.

                    </p>


                    <span class="card-link">
                        Akademisyenleri Yönet →
                    </span>


                </a>



                <!-- FAKÜLTE -->

                <a
                    href="/student_project/admin/faculties/index.php"
                    class="management-card"
                >


                    <div class="card-top">

                        <div class="card-icon">
                            🏫
                        </div>

                        <h3>
                            Fakülte / Bölüm İşlemleri
                        </h3>

                    </div>


                    <p>

                        Fakülteleri ve bunlara bağlı
                        bölümleri görüntüleyebilir,
                        ekleyebilir, düzenleyebilir
                        ve akademik yapıyı
                        yönetebilirsiniz.

                    </p>


                    <span class="card-link">
                        Fakülte ve Bölümleri Yönet →
                    </span>


                </a>



                <!-- DERS -->

                <a
                    href="/student_project/admin/courses/index.php"
                    class="management-card"
                >


                    <div class="card-top">

                        <div class="card-icon">
                            📚
                        </div>

                        <h3>
                            Ders İşlemleri
                        </h3>

                    </div>


                    <p>

                        Dersleri görüntüleyebilir,
                        yeni ders oluşturabilir,
                        dersleri ilgili bölümlere
                        ve akademisyenlere
                        atayabilirsiniz.

                    </p>


                    <span class="card-link">
                        Dersleri Yönet →
                    </span>


                </a>


            </div>


        </section>



        <!-- ======================================
             SİSTEM DURUMU
        ======================================= -->

        <div class="system-info">


            <div class="system-text">

                <strong>
                    Üniversite Öğrenci Takip Sistemi
                </strong>

                <span>
                    Yönetim paneli
                </span>

            </div>


            <div class="system-status">

                <span class="status-dot"></span>

                Sistem Aktif

            </div>


        </div>


    </main>


</div>


</body>

</html>