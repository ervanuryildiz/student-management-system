<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "ogrenci") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$ogrenciNo = $_SESSION["kullanici"] ?? "";
$ogrenciAdSoyad = $_SESSION["ad_soyad"] ?? "Öğrenci";

$hata = "";
$basarili = "";


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

            $stmt = $baglanti->prepare("
                SELECT sifre
                FROM ogrenci
                WHERE ogrenciNo = :ogrenciNo
                LIMIT 1
            ");

            $stmt->execute([
                "ogrenciNo" => $ogrenciNo
            ]);

            $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$ogrenci) {

                $hata = "Öğrenci kaydı bulunamadı.";

            } else {

                $veritabanindakiSifre = $ogrenci["sifre"];

                $sifreDogru = false;


                if (
                    password_verify(
                        $mevcutSifre,
                        $veritabanindakiSifre
                    )
                ) {

                    $sifreDogru = true;

                } elseif (
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

                    $yeniSifreHash = password_hash(
                        $yeniSifre,
                        PASSWORD_DEFAULT
                    );


                    $stmt = $baglanti->prepare("
                        UPDATE ogrenci
                        SET sifre = :sifre
                        WHERE ogrenciNo = :ogrenciNo
                    ");

                    $stmt->execute([
                        "sifre" => $yeniSifreHash,
                        "ogrenciNo" => $ogrenciNo
                    ]);


                    $basarili =
                        "Şifreniz başarıyla değiştirildi.";
                }
            }

        } catch (PDOException $e) {

            $hata =
                "Şifre değiştirilirken bir veritabanı hatası oluştu: "
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


/* FORM */

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
}

.form-group input {
    width: 100%;
    height: 44px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    padding: 0 13px;
    font-size: 14px;
    outline: none;
}

.form-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
}

.info {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 13px;
    border-radius: 7px;
    color: #64748b;
    font-size: 12px;
    margin-bottom: 20px;
}

.btn {
    border: none;
    background: #2563eb;
    color: white;
    padding: 11px 18px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 600;
}

.btn:hover {
    background: #1d4ed8;
}


/* ALERT */

.alert {
    max-width: 650px;
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}


/* RESPONSIVE */

@media(max-width:800px) {

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

<?php
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="main">


    <header class="header">

        <div class="header-title">

            <h2>Şifre Değiştir</h2>

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

            <h1>Şifre Değiştir</h1>

            <p>
                Hesabınızın giriş şifresini buradan değiştirebilirsiniz.
            </p>

        </div>


        <?php if ($hata !== ""): ?>

            <div class="alert error">
                <?php echo htmlspecialchars($hata); ?>
            </div>

        <?php endif; ?>


        <?php if ($basarili !== ""): ?>

            <div class="alert success">
                <?php echo htmlspecialchars($basarili); ?>
            </div>

        <?php endif; ?>


        <div class="password-card">


            <div class="card-header">

                <h2>Güvenlik Bilgileri</h2>

                <p>
                    Şifrenizi değiştirmek için mevcut şifrenizi doğrulayın.
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
                            minlength="6"
                            required
                        >

                    </div>


                    <div class="info">
                        🔒 Yeni şifreniz en az 6 karakter olmalı
                        ve mevcut şifrenizden farklı olmalıdır.
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