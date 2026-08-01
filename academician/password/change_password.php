<?php

session_start();

// ==========================================
// SADECE AKADEMİSYEN ERİŞEBİLİR
// ==========================================

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "akademisyen") {
    header("Location: /student_project/login.php");
    exit;
}


// ==========================================
// VERİTABANI BAĞLANTISI
// change_password.php:
// student_project/admin/academicians/
// database.php:
// student_project/database.php
// Bu yüzden 2 klasör yukarı çıkıyoruz.
// ==========================================

require_once __DIR__ . '/../../database.php';


// ==========================================
// AKADEMİSYEN BİLGİLERİ
// ==========================================

$akademisyen = $_SESSION["kullanici"] ?? "";
$akademisyenAdSoyad = $_SESSION["ad_soyad"] ?? "Akademisyen";

$hata = "";
$basarili = "";


// ==========================================
// ŞİFRE DEĞİŞTİRME
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mevcutSifre = trim($_POST["mevcutSifre"] ?? "");
    $yeniSifre = trim($_POST["yeniSifre"] ?? "");
    $yeniSifreTekrar = trim($_POST["yeniSifreTekrar"] ?? "");


    if (
        $mevcutSifre === "" ||
        $yeniSifre === "" ||
        $yeniSifreTekrar === ""
    ) {

        $hata = "Lütfen tüm alanları doldurunuz.";

    } elseif (strlen($yeniSifre) < 6) {

        $hata = "Yeni şifre en az 6 karakter olmalıdır.";

    } elseif ($yeniSifre !== $yeniSifreTekrar) {

        $hata = "Yeni şifreler birbiriyle eşleşmiyor.";

    } elseif ($mevcutSifre === $yeniSifre) {

        $hata = "Yeni şifre mevcut şifrenizden farklı olmalıdır.";

    } else {

        try {

            // ==========================================
            // AKADEMİSYENİN MEVCUT ŞİFRESİNİ GETİR
            // ==========================================

            $stmt = $baglanti->prepare("
                SELECT sifre
                FROM akademisyen
                WHERE kullaniciAdi = :kullaniciAdi
                LIMIT 1
            ");

            $stmt->execute([
                "kullaniciAdi" => $akademisyen
            ]);

            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$kullanici) {

                $hata = "Akademisyen kaydı bulunamadı.";

            } else {

                $veritabanindakiSifre = $kullanici["sifre"];

                $sifreDogru = false;


                // Hash olarak kayıtlıysa
                if (
                    password_verify(
                        $mevcutSifre,
                        $veritabanindakiSifre
                    )
                ) {

                    $sifreDogru = true;

                }

                // Eski sistemde düz metin kayıtlıysa
                elseif (
                    hash_equals(
                        (string)$veritabanindakiSifre,
                        (string)$mevcutSifre
                    )
                ) {

                    $sifreDogru = true;
                }


                if (!$sifreDogru) {

                    $hata = "Mevcut şifreniz yanlış.";

                } else {

                    // ==========================================
                    // YENİ ŞİFREYİ HASHLE
                    // ==========================================

                    $yeniSifreHash = password_hash(
                        $yeniSifre,
                        PASSWORD_DEFAULT
                    );


                    // ==========================================
                    // VERİTABANINI GÜNCELLE
                    // ==========================================

                    $stmt = $baglanti->prepare("
                        UPDATE akademisyen
                        SET sifre = :sifre
                        WHERE kullaniciAdi = :kullaniciAdi
                    ");

                    $stmt->execute([
                        "sifre" => $yeniSifreHash,
                        "kullaniciAdi" => $akademisyen
                    ]);


                    $basarili = "Şifreniz başarıyla değiştirildi.";
                }
            }

        } catch (PDOException $e) {

            $hata = "Şifre değiştirilirken bir veritabanı hatası oluştu: "
                  . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Şifre Değiştir</title>

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

/* =========================
   SIDEBAR
========================= */

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

/* =========================
   MAIN
========================= */

.main {
    margin-left: 250px;
    min-height: 100vh;
}

/* =========================
   HEADER
========================= */

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

/* =========================
   CONTENT
========================= */

.content {
    padding: 35px;
    max-width: 1450px;
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

/* =========================
   PASSWORD CARD
========================= */

.password-card {
    max-width: 650px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    padding: 22px 25px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h2 {
    font-size: 18px;
    color: #0f172a;
    margin-bottom: 5px;
}

.card-header p {
    color: #64748b;
    font-size: 13px;
}

.card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

.form-group input {
    width: 100%;
    height: 44px;
    padding: 0 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 14px;
    outline: none;
    transition: .2s;
}

.form-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
}

.password-info {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 13px;
    border-radius: 7px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
    margin-bottom: 20px;
}

.btn {
    border: none;
    background: #2563eb;
    color: white;
    padding: 11px 18px;
    border-radius: 7px;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
}

.btn:hover {
    background: #1d4ed8;
}

/* =========================
   MESAJLAR
========================= */

.alert {
    max-width: 650px;
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* =========================
   RESPONSIVE
========================= */

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

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🎓
        </div>

        <div class="logo-text">
            <h2>Öğrenci Takip</h2>
            <span>Yönetim Sistemi</span>
        </div>

    </div>

    <div class="menu-title">
        Akademisyen Menü
    </div>

    <nav class="sidebar-menu">

        <a href="/student_project/admin/academicians/dashboard.php">
            <span class="menu-icon">▦</span>
            <span>Dashboard</span>
        </a>

        <a href="/student_project/admin/academicians/courses/index.php">
            <span class="menu-icon">📚</span>
            <span>Derslerim</span>
        </a>

        <a href="/student_project/admin/academicians/grades/index.php">
            <span class="menu-icon">📝</span>
            <span>Not İşlemleri</span>
        </a>

        <a
            href="/student_project/admin/academicians/change_password.php"
            class="active"
        >
            <span class="menu-icon">🔒</span>
            <span>Şifre Değiştir</span>
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


<!-- MAIN -->

<div class="main">

    <header class="header">

        <div class="header-title">

            <h2>Şifre Değiştir</h2>

            <p>
                Üniversite Öğrenci Takip Sistemi
            </p>

        </div>

        <div class="admin-profile">

            <div class="admin-text">

                <strong>
                    <?php echo htmlspecialchars($akademisyenAdSoyad); ?>
                </strong>

                <span>
                    Akademisyen
                </span>

            </div>

            <div class="avatar">

                <?php
                echo htmlspecialchars(
                    mb_strtoupper(
                        mb_substr(
                            $akademisyenAdSoyad,
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

            <h1>Şifre Değiştir</h1>

            <p>
                Hesabınızın giriş şifresini buradan değiştirebilirsiniz.
            </p>

        </div>


        <?php if ($hata !== ""): ?>

            <div class="alert alert-error">
                <?php echo htmlspecialchars($hata); ?>
            </div>

        <?php endif; ?>


        <?php if ($basarili !== ""): ?>

            <div class="alert alert-success">
                <?php echo htmlspecialchars($basarili); ?>
            </div>

        <?php endif; ?>


        <div class="password-card">

            <div class="card-header">

                <h2>Güvenlik Bilgileri</h2>

                <p>
                    Yeni şifrenizi belirlemek için mevcut şifrenizi doğrulayın.
                </p>

            </div>


            <div class="card-body">

                <form method="POST">

                    <div class="form-group">

                        <label for="mevcutSifre">
                            Mevcut Şifre
                        </label>

                        <input
                            type="password"
                            id="mevcutSifre"
                            name="mevcutSifre"
                            placeholder="Mevcut şifrenizi giriniz"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="yeniSifre">
                            Yeni Şifre
                        </label>

                        <input
                            type="password"
                            id="yeniSifre"
                            name="yeniSifre"
                            placeholder="Yeni şifrenizi giriniz"
                            minlength="6"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="yeniSifreTekrar">
                            Yeni Şifre Tekrar
                        </label>

                        <input
                            type="password"
                            id="yeniSifreTekrar"
                            name="yeniSifreTekrar"
                            placeholder="Yeni şifrenizi tekrar giriniz"
                            minlength="6"
                            required
                        >

                    </div>


                    <div class="password-info">
                        🔒 Yeni şifreniz en az 6 karakter olmalı ve mevcut
                        şifrenizden farklı olmalıdır.
                    </div>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Şifreyi Değiştir
                    </button>

                </form>

            </div>

        </div>

    </main>

</div>

</body>
</html>