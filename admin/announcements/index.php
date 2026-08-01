<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$adminAdSoyad = $_SESSION["ad_soyad"] ?? "Admin";

$stmt = $baglanti->prepare("
    SELECT
        du.duyuruId,
        du.baslik,
        du.icerik,
        du.duyuruTuru,
        du.dersKodu,
        du.yayinlayan,
        du.yayinlayanRol,
        du.olusturmaTarihi,
        du.guncellemeTarihi,
        d.dersAdi,
        a.ad,
        a.soyad

    FROM duyuru du

    LEFT JOIN ders d
        ON du.dersKodu = d.dersKodu

    LEFT JOIN admin a
        ON du.yayinlayan = a.kullaniciAdi

    ORDER BY du.olusturmaTarihi DESC
");

$stmt->execute();

$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Duyuru Yönetimi</title>

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
    max-width: 1400px;
    margin: auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 27px;
    color: #0f172a;
    margin-bottom: 6px;
}

.page-header p {
    color: #64748b;
    font-size: 14px;
}

.add-btn {
    background: #16a34a;
    color: white;
    padding: 11px 17px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
}

.add-btn:hover {
    background: #15803d;
}

/* ALERT */

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
}

.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
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
}

.table-header p {
    color: #64748b;
    font-size: 13px;
    margin-top: 5px;
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
    text-align: left;
}

th,
td {
    padding: 14px;
    border-bottom: 1px solid #e2e8f0;
}

td {
    font-size: 13px;
}

.title {
    font-weight: 600;
    color: #0f172a;
}

.description {
    margin-top: 5px;
    color: #64748b;
    font-size: 12px;
    max-width: 350px;
}

.badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.general {
    background: #dcfce7;
    color: #15803d;
}

.course {
    background: #dbeafe;
    color: #1d4ed8;
}

.actions {
    display: flex;
    gap: 6px;
}

.btn {
    padding: 7px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.edit {
    background: #2563eb;
    color: white;
}

.delete {
    background: #fee2e2;
    color: #dc2626;
}

.empty {
    padding: 45px;
    text-align: center;
    color: #64748b;
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
        Yönetim
    </div>

    <nav class="sidebar-menu">

        <a href="/student_project/admin/dashboard.php">
            <span class="menu-icon">▦</span>
            Dashboard
        </a>

        <a href="/student_project/admin/students/index.php">
            <span class="menu-icon">👥</span>
            Öğrenci İşlemleri
        </a>

        <a href="/student_project/admin/academicians/index.php">
            <span class="menu-icon">👨‍🏫</span>
            Akademisyen İşlemleri
        </a>

        <a href="/student_project/admin/courses/index.php">
            <span class="menu-icon">📚</span>
            Ders İşlemleri
        </a>

        <a
            href="/student_project/admin/announcements/index.php"
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

    <div class="header-title">

        <h2>Duyuru Yönetimi</h2>

        <p>
            Üniversite Öğrenci Takip Sistemi
        </p>

    </div>

    <div class="admin-profile">

        <div class="admin-text">

            <strong>
                <?php echo htmlspecialchars($adminAdSoyad); ?>
            </strong>

            <span>Sistem Yöneticisi</span>

        </div>

        <div class="avatar">
            <?php
            echo htmlspecialchars(
                mb_strtoupper(
                    mb_substr($adminAdSoyad, 0, 1, "UTF-8"),
                    "UTF-8"
                )
            );
            ?>
        </div>

    </div>

</header>


<main class="content">

    <div class="page-header">

        <div>

            <h1>Duyurular</h1>

            <p>
                Sistem ve ders duyurularını buradan yönetebilirsiniz.
            </p>

        </div>

        <a
    href="/student_project/admin/announcements/add.php"
    class="add-btn"
>
    + Yeni Duyuru
</a>

    </div>


    <?php if (isset($_GET["durum"])): ?>

        <?php if ($_GET["durum"] === "eklendi"): ?>

            <div class="alert success">
                Duyuru başarıyla yayınlandı.
            </div>

        <?php elseif ($_GET["durum"] === "guncellendi"): ?>

            <div class="alert success">
                Duyuru başarıyla güncellendi.
            </div>

        <?php elseif ($_GET["durum"] === "silindi"): ?>

            <div class="alert success">
                Duyuru başarıyla silindi.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <div class="table-card">

        <div class="table-header">

            <h2>Yayınlanan Duyurular</h2>

            <p>
                Sistemde yayınlanan tüm duyurular.
            </p>

        </div>


        <?php if (count($duyurular) > 0): ?>

        <div class="table-wrapper">

        <table>

            <thead>

            <tr>
                <th>Duyuru</th>
                <th>Tür</th>
                <th>Ders</th>
                <th>Yayınlayan</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>

            </thead>

            <tbody>

            <?php foreach ($duyurular as $duyuru): ?>

                <tr>

                    <td>

                        <div class="title">
                            <?php
                            echo htmlspecialchars($duyuru["baslik"]);
                            ?>
                        </div>

                        <div class="description">

                            <?php

                            $icerik = $duyuru["icerik"];

                            echo htmlspecialchars(
                                mb_strlen($icerik) > 80
                                    ? mb_substr($icerik, 0, 80) . "..."
                                    : $icerik
                            );

                            ?>

                        </div>

                    </td>


                    <td>

                        <?php if ($duyuru["duyuruTuru"] === "genel"): ?>

                            <span class="badge general">
                                Genel
                            </span>

                        <?php else: ?>

                            <span class="badge course">
                                Ders
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php if ($duyuru["dersKodu"]): ?>

                            <strong>
                                <?php
                                echo htmlspecialchars($duyuru["dersKodu"]);
                                ?>
                            </strong>

                            <br>

                            <small>
                                <?php
                                echo htmlspecialchars(
                                    $duyuru["dersAdi"] ?? ""
                                );
                                ?>
                            </small>

                        <?php else: ?>

                            -

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php

                        if ($duyuru["ad"] && $duyuru["soyad"]) {

                            echo htmlspecialchars(
                                $duyuru["ad"] . " " . $duyuru["soyad"]
                            );

                        } else {

                            echo htmlspecialchars($duyuru["yayinlayan"]);
                        }

                        ?>

                        <br>

                        <small>
                            <?php
                            echo htmlspecialchars(
                                ucfirst($duyuru["yayinlayanRol"])
                            );
                            ?>
                        </small>

                    </td>


                    <td>

                        <?php
                        echo date(
                            "d.m.Y H:i",
                            strtotime($duyuru["olusturmaTarihi"])
                        );
                        ?>

                    </td>


                    <td>

                        <div class="actions">

                            <a
                                href="edit.php?id=<?php
                                echo (int)$duyuru["duyuruId"];
                                ?>"
                                class="btn edit"
                            >
                                Düzenle
                            </a>

                            <a
                                href="delete.php?id=<?php
                                echo (int)$duyuru["duyuruId"];
                                ?>"
                                class="btn delete"
                                onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');"
                            >
                                Sil
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        </div>

        <?php else: ?>

            <div class="empty">
                Henüz yayınlanmış bir duyuru bulunmamaktadır.
            </div>

        <?php endif; ?>

    </div>

</main>

</div>

</body>
</html>