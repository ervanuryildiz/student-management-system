<?php

session_start();

// ==========================================
// SADECE ADMIN ERİŞEBİLİR
// ==========================================

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$adminAdSoyad = $_SESSION["ad_soyad"] ?? "Admin";
$adminKullaniciAdi = $_SESSION["kullanici"] ?? "";

$hata = "";


// ==========================================
// DERSLERİ GETİR
// ==========================================

$stmt = $baglanti->prepare("
    SELECT
        dersKodu,
        dersAdi
    FROM ders
    ORDER BY dersAdi ASC
");

$stmt->execute();

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// DUYURU EKLE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $baslik = trim($_POST["baslik"] ?? "");
    $icerik = trim($_POST["icerik"] ?? "");
    $duyuruTuru = trim($_POST["duyuruTuru"] ?? "genel");
    $hedefKitle = trim($_POST["hedefKitle"] ?? "ogrenci");
    $dersKodu = trim($_POST["dersKodu"] ?? "");


    // ======================================
    // KONTROLLER
    // ======================================

    if ($baslik === "") {

        $hata = "Duyuru başlığını giriniz.";

    } elseif ($icerik === "") {

        $hata = "Duyuru içeriğini giriniz.";

    } elseif (!in_array($duyuruTuru, ["genel", "ders"], true)) {

        $hata = "Geçersiz duyuru türü.";

    } elseif (
        $duyuruTuru === "genel"
        &&
        !in_array(
            $hedefKitle,
            ["ogrenci", "akademisyen", "herkes"],
            true
        )
    ) {

        $hata = "Geçersiz hedef kitle.";

    } elseif (
        $duyuruTuru === "ders"
        &&
        $dersKodu === ""
    ) {

        $hata = "Ders duyurusu için bir ders seçiniz.";

    } else {


        // ==================================
        // GENEL DUYURU
        // ==================================

        if ($duyuruTuru === "genel") {

            $dersKodu = null;
        }


        // ==================================
        // DERS DUYURUSU
        // ==================================

        if ($duyuruTuru === "ders") {

            // Ders duyuruları öğrencilere gider
            $hedefKitle = "ogrenci";


            // Ders gerçekten var mı?
            $kontrol = $baglanti->prepare("
                SELECT COUNT(*)
                FROM ders
                WHERE dersKodu = :dersKodu
            ");

            $kontrol->execute([
                "dersKodu" => $dersKodu
            ]);


            if ((int)$kontrol->fetchColumn() === 0) {

                $hata = "Seçilen ders sistemde bulunamadı.";
            }
        }


        // ==================================
        // VERİTABANINA EKLE
        // ==================================

        if ($hata === "") {

            try {

                $stmt = $baglanti->prepare("
                    INSERT INTO duyuru
                    (
                        baslik,
                        icerik,
                        duyuruTuru,
                        hedefKitle,
                        dersKodu,
                        yayinlayan,
                        yayinlayanRol,
                        olusturmaTarihi,
                        guncellemeTarihi
                    )

                    VALUES
                    (
                        :baslik,
                        :icerik,
                        :duyuruTuru,
                        :hedefKitle,
                        :dersKodu,
                        :yayinlayan,
                        :yayinlayanRol,
                        NOW(),
                        NULL
                    )
                ");

                $stmt->execute([

                    "baslik" => $baslik,

                    "icerik" => $icerik,

                    "duyuruTuru" => $duyuruTuru,

                    "hedefKitle" => $hedefKitle,

                    "dersKodu" => $dersKodu,

                    "yayinlayan" => $adminKullaniciAdi,

                    "yayinlayanRol" => "admin"

                ]);


                header(
                    "Location: /student_project/admin/announcements/index.php?durum=eklendi"
                );

                exit;


            } catch (PDOException $e) {

                $hata =
                    "Duyuru eklenirken hata oluştu: "
                    . $e->getMessage();
            }
        }
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

<title>Yeni Duyuru</title>

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


/* CONTENT */

.content {
    padding: 35px;

    max-width: 1050px;

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


/* ERROR */

.error {
    background: #fee2e2;

    border: 1px solid #fecaca;

    color: #991b1b;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;
}


/* FORM CARD */

.card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    overflow: hidden;
}

.card-header {
    padding: 20px 25px;

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


/* FORM */

.form-group {
    margin-bottom: 22px;
}

label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 600;

    color: #334155;
}

input,
select,
textarea {
    width: 100%;

    border: 1px solid #cbd5e1;

    border-radius: 7px;

    padding: 11px 13px;

    font-family: inherit;

    font-size: 14px;

    outline: none;

    background: white;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .10);
}

textarea {
    min-height: 180px;

    resize: vertical;

    line-height: 1.6;
}

.type-info {
    margin-top: 8px;

    font-size: 12px;

    color: #64748b;
}


/* INFO */

.info-box {
    background: #eff6ff;

    border: 1px solid #dbeafe;

    color: #1e40af;

    padding: 14px;

    border-radius: 8px;

    margin-bottom: 22px;

    font-size: 13px;

    line-height: 1.7;
}


/* BUTTONS */

.actions {
    display: flex;

    justify-content: flex-end;

    gap: 10px;

    padding-top: 5px;
}

.btn {
    padding: 10px 17px;

    border-radius: 7px;

    border: none;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;
}

.btn-cancel {
    background: #f1f5f9;

    color: #475569;
}

.btn-cancel:hover {
    background: #e2e8f0;
}

.btn-save {
    background: #16a34a;

    color: white;
}

.btn-save:hover {
    background: #15803d;
}


/* RESPONSIVE */

@media(max-width: 800px) {

    .sidebar {
        width: 75px;

        padding: 20px 10px;
    }

    .logo {
        justify-content: center;
    }

    .logo-text,
    .menu-title,
    .sidebar-menu span:not(.menu-icon) {
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

            <h2>
                Öğrenci Takip
            </h2>

            <span>
                Yönetim Sistemi
            </span>

        </div>

    </div>


    <div class="menu-title">
        Yönetim
    </div>


    <nav class="sidebar-menu">

        <a href="/student_project/admin/dashboard.php">

            <span class="menu-icon">▦</span>

            <span>
                Dashboard
            </span>

        </a>


        <a href="/student_project/admin/students/index.php">

            <span class="menu-icon">👥</span>

            <span>
                Öğrenci İşlemleri
            </span>

        </a>


        <a href="/student_project/admin/academicians/index.php">

            <span class="menu-icon">👨‍🏫</span>

            <span>
                Akademisyen İşlemleri
            </span>

        </a>


        <a href="/student_project/admin/courses/index.php">

            <span class="menu-icon">📚</span>

            <span>
                Ders İşlemleri
            </span>

        </a>


        <a
            href="/student_project/admin/announcements/index.php"
            class="active"
        >

            <span class="menu-icon">📢</span>

            <span>
                Duyurular
            </span>

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

        <h2>
            Yeni Duyuru
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
                Sistem Yöneticisi
            </span>

        </div>


        <div class="avatar">

            <?php

            echo htmlspecialchars(

                mb_strtoupper(

                    mb_substr(
                        $adminAdSoyad,
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

        <h1>
            Yeni Duyuru Yayınla
        </h1>

        <p>
            Duyurunun türünü ve hedef kitlesini
            belirleyerek yayınlayabilirsiniz.
        </p>

    </div>


    <?php if ($hata !== ""): ?>

        <div class="error">

            <?php
            echo htmlspecialchars(
                $hata
            );
            ?>

        </div>

    <?php endif; ?>


    <div class="card">


        <div class="card-header">

            <h2>
                Duyuru Bilgileri
            </h2>

            <p>
                Yayınlanacak duyurunun bilgilerini giriniz.
            </p>

        </div>


        <div class="card-body">


            <form method="POST">


                <!-- BAŞLIK -->

                <div class="form-group">

                    <label>
                        Duyuru Başlığı
                    </label>

                    <input
                        type="text"
                        name="baslik"
                        maxlength="150"
                        placeholder="Örn: Final Sınav Programı"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST["baslik"] ?? ""
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- DUYURU TÜRÜ -->

                <div class="form-group">

                    <label>
                        Duyuru Türü
                    </label>

                    <select
                        name="duyuruTuru"
                        id="duyuruTuru"
                        onchange="duyuruTuruDegisti()"
                        required
                    >

                        <option
                            value="genel"

                            <?php
                            echo (
                                $_POST["duyuruTuru"] ?? "genel"
                            ) === "genel"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Genel Duyuru
                        </option>


                        <option
                            value="ders"

                            <?php
                            echo (
                                $_POST["duyuruTuru"] ?? ""
                            ) === "ders"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Ders Duyurusu
                        </option>

                    </select>


                    <div class="type-info">

                        Genel duyuruda hedef kitleyi
                        seçebilirsiniz. Ders duyurusu
                        yalnızca ilgili derse kayıtlı
                        öğrencilere gösterilir.

                    </div>

                </div>


                <!-- HEDEF KİTLE -->

                <div
                    class="form-group"
                    id="hedefKitleAlani"
                >

                    <label>
                        Hedef Kitle
                    </label>

                    <select
                        name="hedefKitle"
                        id="hedefKitle"
                    >

                        <option
                            value="ogrenci"

                            <?php
                            echo (
                                $_POST["hedefKitle"] ?? "ogrenci"
                            ) === "ogrenci"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Öğrenciler
                        </option>


                        <option
                            value="akademisyen"

                            <?php
                            echo (
                                $_POST["hedefKitle"] ?? ""
                            ) === "akademisyen"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Akademisyenler
                        </option>


                        <option
                            value="herkes"

                            <?php
                            echo (
                                $_POST["hedefKitle"] ?? ""
                            ) === "herkes"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Herkes
                        </option>

                    </select>


                    <div class="type-info">

                        Duyurunun kimler tarafından
                        görüntülenebileceğini seçiniz.

                    </div>

                </div>


                <!-- DERS -->

                <div
                    class="form-group"
                    id="dersAlani"
                    style="display:none;"
                >

                    <label>
                        Ders Seçiniz
                    </label>

                    <select
                        name="dersKodu"
                        id="dersKodu"
                    >

                        <option value="">
                            -- Ders Seçiniz --
                        </option>


                        <?php foreach ($dersler as $ders): ?>

                            <option
                                value="<?php
                                echo htmlspecialchars(
                                    $ders["dersKodu"]
                                );
                                ?>"

                                <?php

                                echo (
                                    ($_POST["dersKodu"] ?? "")
                                    ===
                                    $ders["dersKodu"]
                                )
                                    ? "selected"
                                    : "";

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(

                                    $ders["dersKodu"]
                                    . " - "
                                    . $ders["dersAdi"]

                                );

                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>


                    <div class="type-info">

                        Ders duyurusu yalnızca bu derse
                        kayıtlı öğrencilere gösterilir.

                    </div>

                </div>


                <!-- İÇERİK -->

                <div class="form-group">

                    <label>
                        Duyuru İçeriği
                    </label>

                    <textarea
                        name="icerik"
                        maxlength="5000"
                        placeholder="Duyuru içeriğini yazınız..."
                        required
                    ><?php
                    echo htmlspecialchars(
                        $_POST["icerik"] ?? ""
                    );
                    ?></textarea>

                </div>


                <!-- BİLGİ -->

                <div class="info-box">

                    📢 <strong>Genel → Öğrenciler:</strong>
                    Tüm öğrencilere gösterilir.

                    <br><br>

                    👨‍🏫 <strong>Genel → Akademisyenler:</strong>
                    Tüm akademisyenlere gösterilir.

                    <br><br>

                    👥 <strong>Genel → Herkes:</strong>
                    Hem öğrencilere hem akademisyenlere gösterilir.

                    <br><br>

                    📚 <strong>Ders Duyurusu:</strong>
                    Yalnızca seçilen derse kayıtlı
                    öğrencilere gösterilir.

                </div>


                <!-- BUTONLAR -->

                <div class="actions">

                    <a
                        href="/student_project/admin/announcements/index.php"
                        class="btn btn-cancel"
                    >
                        İptal
                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        📢 Duyuruyu Yayınla
                    </button>

                </div>


            </form>


        </div>

    </div>


</main>

</div>


<script>

function duyuruTuruDegisti() {

    const duyuruTuru =
        document.getElementById("duyuruTuru");

    const dersAlani =
        document.getElementById("dersAlani");

    const dersKodu =
        document.getElementById("dersKodu");

    const hedefKitleAlani =
        document.getElementById("hedefKitleAlani");

    const hedefKitle =
        document.getElementById("hedefKitle");


    // ======================================
    // DERS DUYURUSU
    // ======================================

    if (duyuruTuru.value === "ders") {

        dersAlani.style.display = "block";

        dersKodu.required = true;


        // Ders duyurusu zaten öğrenciye gider.
        // Hedef kitle seçmeye gerek yok.
        hedefKitleAlani.style.display = "none";

        hedefKitle.required = false;

        hedefKitle.value = "ogrenci";

    }


    // ======================================
    // GENEL DUYURU
    // ======================================

    else {

        dersAlani.style.display = "none";

        dersKodu.required = false;


        hedefKitleAlani.style.display = "block";

        hedefKitle.required = true;
    }
}


duyuruTuruDegisti();

</script>


</body>

</html>