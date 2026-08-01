<?php

require_once __DIR__ . '/../../includes/auth.php';

rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$akademisyen = $_SESSION["kullanici"] ?? "";
$hata = "";


// ==========================================
// AKADEMİSYENİN DERSLERİ
// ==========================================

$stmt = $baglanti->prepare("
    SELECT dersKodu, dersAdi
    FROM ders
    WHERE akademisyen = :akademisyen
    ORDER BY dersAdi ASC
");

$stmt->execute([
    "akademisyen" => $akademisyen
]);

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// DUYURU EKLE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $baslik = trim($_POST["baslik"] ?? "");
    $icerik = trim($_POST["icerik"] ?? "");
    $dersKodu = trim($_POST["dersKodu"] ?? "");

    if ($baslik === "" || $icerik === "" || $dersKodu === "") {

        $hata = "Lütfen tüm alanları doldurunuz.";

    } else {

        // Seçilen ders gerçekten bu akademisyenin mi?
        $kontrol = $baglanti->prepare("
            SELECT COUNT(*)
            FROM ders
            WHERE dersKodu = :dersKodu
            AND akademisyen = :akademisyen
        ");

        $kontrol->execute([
            "dersKodu" => $dersKodu,
            "akademisyen" => $akademisyen
        ]);

        if ((int)$kontrol->fetchColumn() === 0) {

            $hata = "Bu ders için duyuru yayınlama yetkiniz bulunmamaktadır.";

        } else {

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
                    'ders',
                    'ogrenci',
                    :dersKodu,
                    :yayinlayan,
                    'akademisyen',
                    NOW(),
                    NULL
                )
            ");

            $stmt->execute([
                "baslik" => $baslik,
                "icerik" => $icerik,
                "dersKodu" => $dersKodu,
                "yayinlayan" => $akademisyen
            ]);

            header(
                "Location: /student_project/admin/academicians/announcements/index.php?tab=gonderilen&durum=eklendi"
            );
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
}

a {
    text-decoration: none;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background: #0f172a;
    padding: 25px 18px;
    color: white;
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
}

.logo span {
    color: #94a3b8;
    font-size: 11px;
}

.menu-title {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    margin: 20px 10px 10px;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sidebar-menu a {
    display: flex;
    gap: 12px;
    align-items: center;
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
    background: #dc2626;
    color: white;
    text-align: center;
    border-radius: 8px;
    font-size: 13px;
}

.main {
    margin-left: 250px;
}

.header {
    height: 72px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 35px;
}

.header h2 {
    font-size: 18px;
    color: #0f172a;
}

.header p {
    color: #94a3b8;
    font-size: 12px;
    margin-top: 3px;
}

.profile {
    text-align: right;
}

.profile strong {
    display: block;
    font-size: 13px;
}

.profile span {
    font-size: 11px;
    color: #94a3b8;
}

.content {
    max-width: 900px;
    margin: auto;
    padding: 35px;
}

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    color: #0f172a;
    margin-bottom: 7px;
}

.page-header p {
    color: #64748b;
    font-size: 14px;
}

.error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
}

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

label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 7px;
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
}

input:focus,
select:focus,
textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
}

textarea {
    min-height: 180px;
    resize: vertical;
}

.info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #dbeafe;
    padding: 13px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    border: 0;
    border-radius: 7px;
    padding: 10px 17px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.cancel {
    background: #f1f5f9;
    color: #475569;
}

.save {
    background: #2563eb;
    color: white;
}

</style>

</head>

<body>

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">🎓</div>

        <div>
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
            Dashboard
        </a>

        <a href="/student_project/admin/academicians/courses/index.php">
            <span class="menu-icon">📚</span>
            Derslerim
        </a>

        <a href="/student_project/admin/academicians/students/index.php">
            <span class="menu-icon">👥</span>
            Öğrencilerim
        </a>

        <a href="/student_project/admin/academicians/grades/index.php">
            <span class="menu-icon">📝</span>
            Not İşlemleri
        </a>

        <a
            href="/student_project/admin/academicians/announcements/index.php"
            class="active"
        >
            <span class="menu-icon">📢</span>
            Duyurular
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


<div class="main">

<header class="header">

    <div>
        <h2>Yeni Duyuru</h2>
        <p>Üniversite Öğrenci Takip Sistemi</p>
    </div>

    <div class="profile">

        <strong>
            <?php echo htmlspecialchars($akademisyenAdSoyad); ?>
        </strong>

        <span>Akademisyen</span>

    </div>

</header>


<main class="content">

    <div class="page-header">

        <h1>Yeni Duyuru Yayınla</h1>

        <p>
            Dersinizdeki öğrencilere duyuru yayınlayabilirsiniz.
        </p>

    </div>


    <?php if ($hata !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($hata); ?>
        </div>

    <?php endif; ?>


    <div class="card">

        <div class="card-header">

            <h2>Duyuru Bilgileri</h2>

            <p>
                Duyurunun yayınlanacağı dersi ve içeriği belirleyin.
            </p>

        </div>


        <div class="card-body">

            <form method="POST">


                <div class="form-group">

                    <label>Ders</label>

                    <select name="dersKodu" required>

                        <option value="">
                            -- Ders Seçiniz --
                        </option>

                        <?php foreach ($dersler as $ders): ?>

                            <option
                                value="<?php echo htmlspecialchars($ders["dersKodu"]); ?>"
                                <?php
                                echo ($_POST["dersKodu"] ?? "") === $ders["dersKodu"]
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

                </div>


                <div class="form-group">

                    <label>Başlık</label>

                    <input
                        type="text"
                        name="baslik"
                        maxlength="150"
                        value="<?php
                        echo htmlspecialchars($_POST["baslik"] ?? "");
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Duyuru İçeriği</label>

                    <textarea
                        name="icerik"
                        maxlength="5000"
                        required
                    ><?php
                    echo htmlspecialchars($_POST["icerik"] ?? "");
                    ?></textarea>

                </div>


                <div class="info">
                    📢 Bu duyuru yalnızca seçtiğiniz derse kayıtlı
                    öğrenciler tarafından görüntülenebilecektir.
                </div>


                <div class="actions">

                    <a
                        href="index.php?tab=gonderilen"
                        class="btn cancel"
                    >
                        İptal
                    </a>

                    <button
                        type="submit"
                        class="btn save"
                    >
                        📢 Duyuruyu Yayınla
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>

</div>

</body>

</html>