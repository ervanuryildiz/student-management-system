<?php

session_start();

// Sadece öğrenci erişebilir
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "ogrenci") {
    header("Location: /student_project/login.php");
    exit;
}

$ogrenciAdSoyad = $_SESSION["ad_soyad"] ?? "Öğrenci";
$ogrenciNo = $_SESSION["kullanici"] ?? "";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Öğrenci Paneli</title>

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
    margin-bottom: 30px;
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
   HOŞ GELDİN KARTI
========================================== */

.welcome-card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 25px;

    margin-bottom: 25px;
}


.welcome-card h2 {
    color: #0f172a;

    font-size: 20px;

    margin-bottom: 8px;
}


.welcome-card p {
    color: #64748b;

    font-size: 14px;

    line-height: 1.6;
}


/* ==========================================
   İŞLEM KARTLARI
========================================== */

.menu-grid {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;
}


.menu-card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 25px;

    color: #1e293b;

    transition: .2s;
}


.menu-card:hover {
    transform: translateY(-3px);

    box-shadow: 0 8px 20px rgba(0, 0, 0, .07);

    border-color: #cbd5e1;
}


.card-icon {
    width: 48px;
    height: 48px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eff6ff;

    border-radius: 10px;

    font-size: 24px;

    margin-bottom: 18px;
}


.menu-card h3 {
    color: #0f172a;

    font-size: 17px;

    margin-bottom: 8px;
}


.menu-card p {
    color: #64748b;

    font-size: 13px;

    line-height: 1.6;
}


.card-link {
    display: inline-block;

    margin-top: 18px;

    color: #2563eb;

    font-size: 13px;

    font-weight: 600;
}


/* ==========================================
   RESPONSIVE
========================================== */

@media(max-width: 1000px) {

    .menu-grid {
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


    .menu-grid {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<?php
require_once __DIR__ . '/../includes/sidebar.php';
?>


<!-- ==========================================
     MAIN
========================================== -->

<div class="main">


    <!-- HEADER -->

    <header class="header">


        <div class="header-title">

            <h2>
                Öğrenci Paneli
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
                    echo $ogrenciNo !== ""
                        ? htmlspecialchars($ogrenciNo)
                        : "Öğrenci";
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


    <!-- CONTENT -->

    <main class="content">


        <div class="page-header">

            <h1>
                Öğrenci Paneli
            </h1>

            <p>
                Ders ve not bilgilerinize buradan ulaşabilirsiniz.
            </p>

        </div>


        <!-- HOŞ GELDİN -->

        <div class="welcome-card">

            <h2>

                Hoş geldiniz,
                <?php
                echo htmlspecialchars(
                    $ogrenciAdSoyad
                );
                ?>

            </h2>

            <p>
                Kayıtlı olduğunuz dersleri ve akademik
                notlarınızı görüntüleyebilir, hesap
                şifrenizi değiştirebilirsiniz.
            </p>

        </div>


        <!-- İŞLEMLER -->

        <div class="menu-grid">


            <!-- DERSLERİM -->

            <a
                href="/student_project/student/courses/index.php"
                class="menu-card"
            >

                <div class="card-icon">
                    📚
                </div>

                <h3>
                    Derslerim
                </h3>

                <p>
                    Kayıtlı olduğunuz dersleri,
                    ders kodlarını ve ders
                    akademisyenlerini görüntüleyebilirsiniz.
                </p>

                <span class="card-link">
                    Dersleri Görüntüle →
                </span>

            </a>


            <!-- NOTLARIM -->

            <a
                href="/student_project/student/grades/index.php"
                class="menu-card"
            >

                <div class="card-icon">
                    📝
                </div>

                <h3>
                    Notlarım
                </h3>

                <p>
                    Derslerinize ait vize, final,
                    ortalama, harf notu ve başarı
                    durumunuzu görüntüleyebilirsiniz.
                </p>

                <span class="card-link">
                    Notları Görüntüle →
                </span>

            </a>

            <!-- DUYURULAR -->

<a
    href="/student_project/student/announcements/index.php"
    class="menu-card"
>

    <div class="card-icon">
        📢
    </div>

    <h3>
        Duyurular
    </h3>

    <p>
        Yönetim ve akademisyenler tarafından
        yayınlanan genel ve ders duyurularını
        görüntüleyebilirsiniz.
    </p>

    <span class="card-link">
        Duyuruları Görüntüle →
    </span>

</a>
            


            <!-- ŞİFRE DEĞİŞTİR -->

            <a
                href="/student_project/student/password/index.php"
                class="menu-card"
            >

                <div class="card-icon">
                    🔒
                </div>

                <h3>
                    Şifre Değiştir
                </h3>

                <p>
                    Mevcut şifrenizi doğrulayarak
                    hesabınız için yeni bir giriş
                    şifresi belirleyebilirsiniz.
                </p>

                <span class="card-link">
                    Şifreyi Değiştir →
                </span>

            </a>


        </div>


    </main>


</div>


</body>

</html>