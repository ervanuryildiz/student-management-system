<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$hata = "";

$ogrenciNo = "";
$ad = "";
$soyad = "";
$bolum = "";


// =====================================================
// BÖLÜMLERİ GETİR
// =====================================================

$bolumler = [];

try {

    $stmtBolum = $baglanti->query("
        SELECT DISTINCT bolum
        FROM ogrenci
        WHERE bolum IS NOT NULL
        AND bolum != ''
        ORDER BY bolum ASC
    ");

    $bolumler = $stmtBolum->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {

    $bolumler = [];

}


// =====================================================
// FORM GÖNDERİLDİ
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ogrenciNo = trim($_POST["ogrenciNo"] ?? "");
    $ad        = trim($_POST["ad"] ?? "");
    $soyad     = trim($_POST["soyad"] ?? "");
    $bolum     = trim($_POST["bolum"] ?? "");

    // Şifrede trim kullanmıyoruz.
    $sifre = $_POST["sifre"] ?? "";


    // -------------------------------------------------
    // BOŞ ALAN KONTROLÜ
    // -------------------------------------------------

    if (
        $ogrenciNo === "" ||
        $ad === "" ||
        $soyad === "" ||
        $bolum === "" ||
        $sifre === ""
    ) {

        $hata = "Lütfen tüm alanları doldurun.";

    }

    // -------------------------------------------------
    // ŞİFRE UZUNLUĞU
    // -------------------------------------------------

    elseif (strlen($sifre) < 6) {

        $hata = "Şifre en az 6 karakter olmalıdır.";

    }

    else {

        try {

            // -------------------------------------------------
            // AYNI ÖĞRENCİ NO VAR MI?
            // -------------------------------------------------

            $kontrol = $baglanti->prepare("
                SELECT ogrenciNo
                FROM ogrenci
                WHERE ogrenciNo = :ogrenciNo
                LIMIT 1
            ");

            $kontrol->execute([
                "ogrenciNo" => $ogrenciNo
            ]);


            if ($kontrol->fetch(PDO::FETCH_ASSOC)) {

                $hata =
                    "Bu öğrenci numarasıyla kayıtlı bir öğrenci zaten bulunmaktadır.";

            }

            else {

                // -------------------------------------------------
                // ŞİFREYİ HASHLE
                // -------------------------------------------------

                $sifreHash = password_hash(
                    $sifre,
                    PASSWORD_DEFAULT
                );


                // Hash oluşturulamadıysa kayıt yapma
                if ($sifreHash === false) {

                    $hata =
                        "Şifre oluşturulurken bir hata meydana geldi.";

                }

                else {

                    // -------------------------------------------------
                    // ÖĞRENCİ EKLE
                    // -------------------------------------------------

                    $sql = "
                        INSERT INTO ogrenci
                        (
                            ogrenciNo,
                            ad,
                            soyad,
                            bolum,
                            sifre
                        )
                        VALUES
                        (
                            :ogrenciNo,
                            :ad,
                            :soyad,
                            :bolum,
                            :sifre
                        )
                    ";

                    $stmt = $baglanti->prepare($sql);

                    $stmt->execute([

                        "ogrenciNo" => $ogrenciNo,
                        "ad"        => $ad,
                        "soyad"     => $soyad,
                        "bolum"     => $bolum,

                        // Düz şifre DEĞİL,
                        // hashlenmiş şifre kaydediliyor.
                        "sifre"     => $sifreHash

                    ]);


                    header(
                        "Location: /student_project/admin/students/index.php?durum=eklendi"
                    );

                    exit;
                }
            }

        } catch (PDOException $e) {

            $hata =
                "Öğrenci eklenirken bir veritabanı hatası oluştu.";
        }
    }
}


$adminAdi = $_SESSION["ad_soyad"] ?? "Yönetici";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Yeni Öğrenci Ekle</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

:root {

    --sidebar: #0f172a;
    --sidebar-light: #1e293b;

    --background: #f5f7fb;

    --card: #ffffff;

    --text: #172033;

    --muted: #64748b;

    --border: #e5eaf1;

    --primary: #2563eb;

    --primary-light: #eff6ff;

    --red: #dc2626;

    --red-light: #fee2e2;

    --green: #16a34a;

}

body {

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background: var(--background);

    color: var(--text);

    min-height: 100vh;

}

a {
    text-decoration: none;
}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 255px;

    height: 100vh;

    position: fixed;

    left: 0;
    top: 0;

    background: var(--sidebar);

    padding: 25px 16px;

    color: white;

    overflow-y: auto;

}

.logo {

    padding: 5px 12px 27px;

    border-bottom:
        1px solid rgba(255,255,255,.09);

    margin-bottom: 20px;

}

.logo-icon {

    width: 43px;
    height: 43px;

    border-radius: 11px;

    background: var(--primary);

    display: flex;

    justify-content: center;
    align-items: center;

    font-size: 22px;

    margin-bottom: 12px;

}

.logo h2 {

    font-size: 17px;

    margin-bottom: 4px;

}

.logo p {

    color: #94a3b8;

    font-size: 12px;

}

.menu-title {

    font-size: 10px;

    color: #64748b;

    letter-spacing: 1.2px;

    font-weight: 700;

    padding: 8px 12px;

}

.sidebar-link {

    color: #cbd5e1;

    padding: 12px 13px;

    border-radius: 8px;

    margin-bottom: 5px;

    display: flex;

    align-items: center;

    gap: 12px;

    font-size: 14px;

    transition: .2s;

}

.sidebar-link:hover {

    background: var(--sidebar-light);

    color: white;

}

.sidebar-link.active {

    background: var(--primary);

    color: white;

}

.sidebar-icon {

    width: 23px;

    text-align: center;

    font-size: 17px;

}

.sidebar-bottom {

    margin-top: 30px;

    padding-top: 20px;

    border-top:
        1px solid rgba(255,255,255,.09);

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 255px;

    min-height: 100vh;

}


/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    height: 76px;

    background: white;

    border-bottom:
        1px solid var(--border);

    padding: 0 35px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.topbar-title h3 {

    font-size: 17px;

    margin-bottom: 3px;

}

.topbar-title p {

    color: var(--muted);

    font-size: 12px;

}

.user-area {

    display: flex;

    align-items: center;

    gap: 12px;

}

.user-avatar {

    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: #dbeafe;

    color: #1d4ed8;

    display: flex;

    justify-content: center;
    align-items: center;

    font-weight: bold;

}

.user-info strong {

    display: block;

    font-size: 13px;

}

.user-info span {

    color: var(--muted);

    font-size: 11px;

}

.logout {

    margin-left: 10px;

    background: var(--red-light);

    color: #b91c1c;

    padding: 9px 13px;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;

}


/* =====================================================
   CONTENT
===================================================== */

.content {

    padding: 32px 35px;

    max-width: 1250px;

    margin: auto;

}

.page-header {

    margin-bottom: 25px;

}

.breadcrumb {

    color: var(--muted);

    font-size: 12px;

    margin-bottom: 10px;

}

.breadcrumb a {

    color: var(--primary);

}

.page-header h1 {

    font-size: 27px;

    margin-bottom: 7px;

}

.page-header p {

    color: var(--muted);

    font-size: 14px;

}


/* =====================================================
   LAYOUT
===================================================== */

.form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 2fr)
        minmax(250px, .8fr);

    gap: 22px;

    align-items: start;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background: white;

    border:
        1px solid var(--border);

    border-radius: 13px;

    box-shadow:
        0 2px 7px rgba(15,23,42,.03);

}

.card-header {

    padding: 20px 23px;

    border-bottom:
        1px solid var(--border);

}

.card-header h2 {

    font-size: 16px;

    margin-bottom: 5px;

}

.card-header p {

    color: var(--muted);

    font-size: 12px;

}

.card-body {

    padding: 24px;

}


/* =====================================================
   FORM
===================================================== */

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 18px;

}

.form-group {

    margin-bottom: 20px;

}

.form-group label {

    display: block;

    font-size: 13px;

    font-weight: 600;

    color: #334155;

    margin-bottom: 8px;

}

.required {

    color: var(--red);

}

.input-wrapper {

    position: relative;

}

.input-icon {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    font-size: 15px;

    pointer-events: none;

}

.form-control {

    width: 100%;

    height: 45px;

    border:
        1px solid #dbe1e8;

    border-radius: 8px;

    padding: 0 13px;

    font-family: inherit;

    font-size: 13px;

    outline: none;

    transition: .2s;

    background: white;

}

.input-wrapper .form-control {

    padding-left: 41px;

}

.form-control:focus {

    border-color: #60a5fa;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.08);

}

.form-control::placeholder {

    color: #94a3b8;

}

.help {

    color: #94a3b8;

    font-size: 11px;

    margin-top: 6px;

}


/* =====================================================
   PASSWORD
===================================================== */

.password-toggle {

    position: absolute;

    right: 12px;

    top: 50%;

    transform: translateY(-50%);

    border: none;

    background: transparent;

    cursor: pointer;

    font-size: 16px;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    border-top:
        1px solid var(--border);

    padding-top: 20px;

    margin-top: 5px;

}

.btn {

    display: inline-flex;

    justify-content: center;
    align-items: center;

    gap: 7px;

    border: none;

    border-radius: 8px;

    padding: 11px 17px;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

}

.btn-primary {

    background: var(--primary);

    color: white;

}

.btn-primary:hover {

    background: #1d4ed8;

}

.btn-secondary {

    background: #f1f5f9;

    color: #475569;

}

.btn-secondary:hover {

    background: #e2e8f0;

}


/* =====================================================
   ERROR
===================================================== */

.error {

    background: var(--red-light);

    border:
        1px solid #fecaca;

    color: #991b1b;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;

}


/* =====================================================
   INFO CARD
===================================================== */

.info-card {

    padding: 22px;

}

.info-icon {

    width: 46px;
    height: 46px;

    border-radius: 11px;

    background: var(--primary-light);

    display: flex;

    justify-content: center;
    align-items: center;

    font-size: 21px;

    margin-bottom: 15px;

}

.info-card h3 {

    font-size: 15px;

    margin-bottom: 9px;

}

.info-card p {

    color: var(--muted);

    font-size: 12px;

    line-height: 1.7;

}

.info-list {

    margin-top: 17px;

    padding-top: 17px;

    border-top:
        1px solid var(--border);

}

.info-item {

    display: flex;

    gap: 9px;

    margin-bottom: 12px;

    font-size: 12px;

    color: #475569;

    line-height: 1.5;

}

.check {

    color: var(--green);

    font-weight: bold;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:900px) {

    .form-layout {

        grid-template-columns: 1fr;

    }

}

@media(max-width:750px) {

    .sidebar {

        display: none;

    }

    .main {

        margin-left: 0;

    }

    .topbar {

        padding: 0 18px;

    }

    .content {

        padding: 24px 18px;

    }

    .form-row {

        grid-template-columns: 1fr;

        gap: 0;

    }

    .user-info {

        display: none;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🎓
        </div>

        <h2>Öğrenci Takip</h2>

        <p>
            Üniversite Yönetim Sistemi
        </p>

    </div>


    <div class="menu-title">
        ANA MENÜ
    </div>


    <a
        href="/student_project/admin/dashboard.php"
        class="sidebar-link"
    >
        <span class="sidebar-icon">🏠</span>
        Dashboard
    </a>


    <a
        href="/student_project/admin/students/index.php"
        class="sidebar-link active"
    >
        <span class="sidebar-icon">🎓</span>
        Öğrenciler
    </a>


    <a
        href="/student_project/admin/academicians/index.php"
        class="sidebar-link"
    >
        <span class="sidebar-icon">👨‍🏫</span>
        Akademisyenler
    </a>


    <a
        href="/student_project/admin/courses/index.php"
        class="sidebar-link"
    >
        <span class="sidebar-icon">📚</span>
        Dersler
    </a>


    <a
        href="/student_project/admin/faculties/index.php"
        class="sidebar-link"
    >
        <span class="sidebar-icon">🏫</span>
        Fakülte / Bölüm
    </a>


    <div class="sidebar-bottom">

        <a
            href="/student_project/logout.php"
            class="sidebar-link"
        >
            <span class="sidebar-icon">🚪</span>
            Çıkış Yap
        </a>

    </div>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


<header class="topbar">

    <div class="topbar-title">

        <h3>Öğrenci Yönetimi</h3>

        <p>
            Yeni öğrenci kaydı
        </p>

    </div>


    <div class="user-area">

        <div class="user-avatar">

            <?php
            echo htmlspecialchars(
                mb_strtoupper(
                    mb_substr(
                        $adminAdi,
                        0,
                        1,
                        "UTF-8"
                    ),
                    "UTF-8"
                )
            );
            ?>

        </div>


        <div class="user-info">

            <strong>
                <?php echo htmlspecialchars($adminAdi); ?>
            </strong>

            <span>
                Sistem Yöneticisi
            </span>

        </div>


        <a
            href="/student_project/logout.php"
            class="logout"
        >
            Çıkış
        </a>

    </div>

</header>


<div class="content">


    <div class="page-header">

        <div class="breadcrumb">

            <a href="/student_project/admin/dashboard.php">
                Dashboard
            </a>

            &nbsp;/&nbsp;

            <a href="/student_project/admin/students/index.php">
                Öğrenciler
            </a>

            &nbsp;/&nbsp;

            Yeni Öğrenci

        </div>


        <h1>
            Yeni Öğrenci Ekle
        </h1>

        <p>
            Sisteme yeni bir öğrenci kaydı oluşturun.
        </p>

    </div>


    <?php if ($hata !== ""): ?>

        <div class="error">

            ⚠️

            <?php echo htmlspecialchars($hata); ?>

        </div>

    <?php endif; ?>


    <div class="form-layout">


        <div class="card">


            <div class="card-header">

                <h2>
                    Öğrenci Bilgileri
                </h2>

                <p>
                    Öğrenciye ait temel bilgileri giriniz.
                </p>

            </div>


            <div class="card-body">


                <form method="POST">


                    <!-- ÖĞRENCİ NO -->

                    <div class="form-group">

                        <label>
                            Öğrenci Numarası
                            <span class="required">*</span>
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                #
                            </span>

                            <input
                                type="text"
                                name="ogrenciNo"
                                class="form-control"
                                placeholder="Örn: 12345"
                                value="<?php
                                echo htmlspecialchars($ogrenciNo);
                                ?>"
                                required
                            >

                        </div>


                        <div class="help">
                            Öğrencinin sisteme giriş yaparken kullanacağı öğrenci numarası.
                        </div>

                    </div>


                    <div class="form-row">


                        <!-- AD -->

                        <div class="form-group">

                            <label>
                                Ad
                                <span class="required">*</span>
                            </label>


                            <div class="input-wrapper">

                                <span class="input-icon">
                                    👤
                                </span>

                                <input
                                    type="text"
                                    name="ad"
                                    class="form-control"
                                    placeholder="Öğrenci adı"
                                    value="<?php
                                    echo htmlspecialchars($ad);
                                    ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- SOYAD -->

                        <div class="form-group">

                            <label>
                                Soyad
                                <span class="required">*</span>
                            </label>


                            <div class="input-wrapper">

                                <span class="input-icon">
                                    👤
                                </span>

                                <input
                                    type="text"
                                    name="soyad"
                                    class="form-control"
                                    placeholder="Öğrenci soyadı"
                                    value="<?php
                                    echo htmlspecialchars($soyad);
                                    ?>"
                                    required
                                >

                            </div>

                        </div>


                    </div>


                    <!-- BÖLÜM -->

                    <div class="form-group">

                        <label>
                            Bölüm
                            <span class="required">*</span>
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                🏫
                            </span>


                            <input
                                type="text"
                                name="bolum"
                                id="bolum"
                                class="form-control"
                                list="bolumListesi"
                                placeholder="Örn: Bilgisayar Mühendisliği"
                                value="<?php
                                echo htmlspecialchars($bolum);
                                ?>"
                                required
                            >


                            <datalist id="bolumListesi">

                                <?php foreach ($bolumler as $mevcutBolum): ?>

                                    <option
                                        value="<?php
                                        echo htmlspecialchars(
                                            $mevcutBolum
                                        );
                                        ?>"
                                    >

                                <?php endforeach; ?>

                            </datalist>

                        </div>


                        <div class="help">
                            Öğrencinin kayıtlı olduğu bölüm.
                        </div>

                    </div>


                    <!-- ŞİFRE -->

                    <div class="form-group">

                        <label>
                            Şifre
                            <span class="required">*</span>
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                🔒
                            </span>

                            <input
                                type="password"
                                name="sifre"
                                id="sifre"
                                class="form-control"
                                placeholder="En az 6 karakter"
                                minlength="6"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="sifreGoster()"
                                title="Şifreyi göster/gizle"
                            >
                                👁
                            </button>

                        </div>


                        <div class="help">
                            En az 6 karakterden oluşan geçici bir şifre belirleyiniz.
                            Şifre güvenlik nedeniyle hashlenerek saklanacaktır.
                        </div>

                    </div>


                    <!-- BUTTON -->

                    <div class="form-actions">


                        <a
                            href="/student_project/admin/students/index.php"
                            class="btn btn-secondary"
                        >
                            ← İptal
                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            ✓ Öğrenciyi Kaydet
                        </button>


                    </div>


                </form>


            </div>

        </div>


        <!-- =================================================
             INFO
        ================================================= -->

        <div class="card info-card">


            <div class="info-icon">
                💡
            </div>


            <h3>
                Öğrenci Kaydı
            </h3>


            <p>
                Öğrenciyi sisteme ekledikten sonra ders
                atamalarını Öğrenci İşlemleri ekranındaki
                <strong>Dersleri Yönet</strong> bölümünden
                yapabilirsiniz.
            </p>


            <div class="info-list">


                <div class="info-item">

                    <span class="check">✓</span>

                    <span>
                        Öğrenci numarası benzersiz olmalıdır.
                    </span>

                </div>


                <div class="info-item">

                    <span class="check">✓</span>

                    <span>
                        Şifreler veritabanında düz metin olarak
                        değil, güvenli hash biçiminde saklanır.
                    </span>

                </div>


                <div class="info-item">

                    <span class="check">✓</span>

                    <span>
                        Öğrenci yalnızca kendi bölümüyle
                        ilişkili derslere kaydedilebilir.
                    </span>

                </div>


                <div class="info-item">

                    <span class="check">✓</span>

                    <span>
                        Akademisyenler yalnızca kendi
                        derslerine kayıtlı öğrencileri
                        görüntüleyebilir.
                    </span>

                </div>


                <div class="info-item">

                    <span class="check">✓</span>

                    <span>
                        Öğrenci kendi panelinden derslerini
                        ve notlarını görüntüleyebilir.
                    </span>

                </div>


            </div>


        </div>


    </div>


</div>


</main>


<script>

function sifreGoster() {

    const sifre =
        document.getElementById("sifre");

    if (sifre.type === "password") {

        sifre.type = "text";

    } else {

        sifre.type = "password";

    }

}

</script>


</body>

</html>