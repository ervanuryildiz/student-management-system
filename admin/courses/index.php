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
// VERİTABANI
// ==========================================
require_once __DIR__ . '/../../database.php';


// ==========================================
// ARAMA
// ==========================================
$arama = trim($_GET["arama"] ?? "");


// ==========================================
// DERSLERİ GETİR
// ==========================================
if ($arama !== "") {

    $sql = "
        SELECT
            dersKodu,
            dersAdi,
            akademisyen
        FROM ders
        WHERE
            dersKodu LIKE :arama1
            OR dersAdi LIKE :arama2
            OR akademisyen LIKE :arama3
        ORDER BY dersKodu ASC
    ";

    $stmt = $baglanti->prepare($sql);

    $aramaDegeri = "%" . $arama . "%";

    $stmt->execute([
        "arama1" => $aramaDegeri,
        "arama2" => $aramaDegeri,
        "arama3" => $aramaDegeri
    ]);

} else {

    $sql = "
        SELECT
            dersKodu,
            dersAdi,
            akademisyen
        FROM ders
        ORDER BY dersKodu ASC
    ";

    $stmt = $baglanti->prepare($sql);
    $stmt->execute();
}

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// TOPLAM DERS
// ==========================================
$stmtSay = $baglanti->query("
    SELECT COUNT(*)
    FROM ders
");

$toplamDers = (int)$stmtSay->fetchColumn();


// ==========================================
// AKADEMİSYEN ATANMIŞ DERS
// ==========================================
$stmtAtanmis = $baglanti->query("
    SELECT COUNT(*)
    FROM ders
    WHERE akademisyen IS NOT NULL
    AND akademisyen <> ''
");

$atanmisDers = (int)$stmtAtanmis->fetchColumn();


// ==========================================
// ATANMAMIŞ DERS
// ==========================================
$atanmamisDers = $toplamDers - $atanmisDers;


// ==========================================
// TOPLAM DERS KAYDI
// ogrenci_ders tablosundaki kayıt sayısı
// ==========================================
$stmtKayit = $baglanti->query("
    SELECT COUNT(*)
    FROM ogrenci_ders
");

$toplamKayit = (int)$stmtKayit->fetchColumn();


// ==========================================
// ADMIN BİLGİLERİ
// ==========================================
$adminAdSoyad = $_SESSION["ad_soyad"] ?? "Admin";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Ders İşlemleri</title>

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


/* ==========================================
   CONTENT
========================================== */

.content {
    padding: 35px;

    max-width: 1450px;

    margin: auto;
}


/* ==========================================
   SAYFA BAŞLIĞI
========================================== */

.page-header {
    display: flex;

    justify-content: space-between;
    align-items: flex-start;

    gap: 20px;

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


.add-btn {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    background: #2563eb;

    color: white;

    padding: 11px 16px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}


.add-btn:hover {
    background: #1d4ed8;
}


/* ==========================================
   İSTATİSTİKLER
========================================== */

.stats {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 17px;

    margin-bottom: 28px;
}


.stat-card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}


.stat-label {
    color: #64748b;

    font-size: 12px;
}


.stat-number {
    display: block;

    color: #0f172a;

    font-size: 25px;

    font-weight: 700;

    margin-top: 5px;
}


.stat-icon {
    width: 44px;
    height: 44px;

    border-radius: 10px;

    background: #eff6ff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;
}


/* ==========================================
   MESAJ
========================================== */

.message {
    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;
}


.success {
    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;
}


.error {
    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;
}


/* ==========================================
   ARAMA
========================================== */

.toolbar {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 17px;

    margin-bottom: 20px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;
}


.search-form {
    display: flex;

    gap: 9px;

    flex: 1;

    max-width: 600px;
}


.search-input {
    flex: 1;

    height: 42px;

    border: 1px solid #cbd5e1;

    border-radius: 8px;

    padding: 0 13px;

    outline: none;

    font-size: 13px;
}


.search-input:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,.08);
}


.search-btn {
    border: none;

    background: #0f172a;

    color: white;

    border-radius: 8px;

    padding: 0 18px;

    cursor: pointer;

    font-weight: 600;
}


.search-btn:hover {
    background: #1e293b;
}


.clear-btn {
    display: flex;

    align-items: center;

    padding: 0 15px;

    background: #e2e8f0;

    color: #475569;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}


.result-count {
    color: #64748b;

    font-size: 12px;
}


/* ==========================================
   TABLO
========================================== */

.table-card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    overflow: hidden;
}


.table-header {
    padding: 18px 20px;

    border-bottom: 1px solid #e2e8f0;
}


.table-header h3 {
    font-size: 15px;

    color: #0f172a;

    margin-bottom: 4px;
}


.table-header p {
    font-size: 12px;

    color: #94a3b8;
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

    letter-spacing: .4px;

    text-align: left;

    padding: 13px 18px;

    border-bottom: 1px solid #e2e8f0;
}


td {
    padding: 15px 18px;

    border-bottom: 1px solid #f1f5f9;

    font-size: 13px;

    vertical-align: middle;
}


tbody tr:hover {
    background: #f8fafc;
}


/* ==========================================
   DERS
========================================== */

.course-cell {
    display: flex;

    align-items: center;

    gap: 11px;
}


.course-icon {
    width: 37px;
    height: 37px;

    border-radius: 9px;

    background: #e0e7ff;

    color: #4338ca;

    display: flex;

    align-items: center;
    justify-content: center;

    font-weight: 700;

    flex-shrink: 0;
}


.course-name {
    color: #0f172a;

    font-weight: 600;
}


.course-code {
    color: #94a3b8;

    font-size: 11px;

    margin-top: 2px;
}


.badge {
    display: inline-block;

    background: #dbeafe;

    color: #1d4ed8;

    border-radius: 20px;

    padding: 5px 9px;

    font-size: 11px;

    font-weight: 600;
}


.badge-empty {
    background: #fef3c7;

    color: #92400e;
}


/* ==========================================
   BUTONLAR
========================================== */

.actions {
    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}


.btn {
    display: inline-block;

    border-radius: 6px;

    padding: 7px 10px;

    font-size: 11px;

    font-weight: 600;
}


.btn-students {
    background: #ede9fe;

    color: #6d28d9;
}


.btn-students:hover {
    background: #ddd6fe;
}


.btn-edit {
    background: #e0f2fe;

    color: #0369a1;
}


.btn-edit:hover {
    background: #bae6fd;
}


.btn-delete {
    background: #fee2e2;

    color: #b91c1c;
}


.btn-delete:hover {
    background: #fecaca;
}


/* ==========================================
   BOŞ
========================================== */

.empty {
    text-align: center;

    padding: 50px 20px;
}


.empty-icon {
    font-size: 40px;

    margin-bottom: 10px;
}


.empty h3 {
    color: #0f172a;

    margin-bottom: 6px;
}


.empty p {
    color: #94a3b8;

    font-size: 13px;
}


/* ==========================================
   RESPONSIVE
========================================== */

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


    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }


    .search-form {
        max-width: none;
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


    .page-header {
        flex-direction: column;
    }


    .search-form {
        flex-direction: column;
    }


    .search-input {
        min-height: 42px;
    }


    .search-btn,
    .clear-btn {
        min-height: 42px;
        justify-content: center;
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


        <a href="/student_project/admin/dashboard.php">

            <span class="menu-icon">▦</span>

            <span>Dashboard</span>

        </a>


        <a href="/student_project/admin/students/index.php">

            <span class="menu-icon">🎓</span>

            <span>Öğrenciler</span>

        </a>


        <a href="/student_project/admin/academicians/index.php">

            <span class="menu-icon">👨‍🏫</span>

            <span>Akademisyenler</span>

        </a>


        <a href="/student_project/admin/faculties/index.php">

            <span class="menu-icon">🏫</span>

            <span>Fakülteler</span>

        </a>


        <!-- BURADA DERSLER ACTIVE -->

        <a
            href="/student_project/admin/courses/index.php"
            class="active"
        >

            <span class="menu-icon">📚</span>

            <span>Dersler</span>

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
     MAIN
========================================== -->

<div class="main">


<header class="header">


    <div class="header-title">

        <h2>
            Ders Yönetimi
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

            $ilkHarf = mb_substr(
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



<main class="content">


<!-- ==========================================
     BAŞLIK
========================================== -->

<div class="page-header">


    <div>

        <h1>
            Ders İşlemleri
        </h1>

        <p>
            Sistemdeki dersleri, akademisyen
            atamalarını ve öğrenci kayıtlarını
            yönetebilirsiniz.
        </p>

    </div>


    <a
        href="/student_project/admin/courses/ders_ekle.php"
        class="add-btn"
    >
        ＋ Yeni Ders Ekle
    </a>


</div>



<!-- ==========================================
     MESAJLAR
========================================== -->

<?php if (isset($_GET["durum"])): ?>


    <?php if ($_GET["durum"] === "eklendi"): ?>

        <div class="message success">
            Ders başarıyla eklendi.
        </div>


    <?php elseif ($_GET["durum"] === "guncellendi"): ?>

        <div class="message success">
            Ders bilgileri başarıyla güncellendi.
        </div>


    <?php elseif ($_GET["durum"] === "silindi"): ?>

        <div class="message success">
            Ders başarıyla silindi.
        </div>


    <?php elseif ($_GET["durum"] === "hata"): ?>

        <div class="message error">
            İşlem gerçekleştirilemedi.
        </div>


    <?php endif; ?>


<?php endif; ?>



<!-- ==========================================
     İSTATİSTİKLER
========================================== -->

<div class="stats">


    <div class="stat-card">

        <div>

            <span class="stat-label">
                Toplam Ders
            </span>

            <span class="stat-number">
                <?php echo $toplamDers; ?>
            </span>

        </div>

        <div class="stat-icon">
            📚
        </div>

    </div>



    <div class="stat-card">

        <div>

            <span class="stat-label">
                Akademisyen Atanmış
            </span>

            <span class="stat-number">
                <?php echo $atanmisDers; ?>
            </span>

        </div>

        <div class="stat-icon">
            👨‍🏫
        </div>

    </div>



    <div class="stat-card">

        <div>

            <span class="stat-label">
                Atanmamış Ders
            </span>

            <span class="stat-number">
                <?php echo $atanmamisDers; ?>
            </span>

        </div>

        <div class="stat-icon">
            !
        </div>

    </div>



    <div class="stat-card">

        <div>

            <span class="stat-label">
                Toplam Ders Kaydı
            </span>

            <span class="stat-number">
                <?php echo $toplamKayit; ?>
            </span>

        </div>

        <div class="stat-icon">
            🎓
        </div>

    </div>


</div>



<!-- ==========================================
     ARAMA
========================================== -->

<div class="toolbar">


    <form
        method="GET"
        action=""
        class="search-form"
    >


        <input
            type="text"
            name="arama"
            class="search-input"
            placeholder="Ders kodu, ders adı veya akademisyen ile ara..."
            value="<?php echo htmlspecialchars($arama); ?>"
        >


        <button
            type="submit"
            class="search-btn"
        >
            Ara
        </button>


        <?php if ($arama !== ""): ?>

            <a
                href="/student_project/admin/courses/index.php"
                class="clear-btn"
            >
                Temizle
            </a>

        <?php endif; ?>


    </form>


    <div class="result-count">

        <?php

        if ($arama !== "") {

            echo count($dersler)
                . " sonuç bulundu";

        } else {

            echo $toplamDers
                . " ders";

        }

        ?>

    </div>


</div>



<!-- ==========================================
     DERS TABLOSU
========================================== -->

<div class="table-card">


    <div class="table-header">

        <h3>
            Ders Listesi
        </h3>

        <p>
            Sistemde tanımlı dersler
        </p>

    </div>



    <?php if (count($dersler) > 0): ?>


        <div class="table-wrapper">


            <table>


                <thead>

                <tr>

                    <th>Ders</th>

                    <th>Ders Kodu</th>

                    <th>Akademisyen</th>

                    <th>İşlemler</th>

                </tr>

                </thead>


                <tbody>


                <?php foreach ($dersler as $ders): ?>


                    <tr>


                        <!-- DERS -->

                        <td>

                            <div class="course-cell">


                                <div class="course-icon">
                                    📚
                                </div>


                                <div>


                                    <div class="course-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $ders["dersAdi"]
                                        );

                                        ?>

                                    </div>


                                    <div class="course-code">

                                        Üniversite Dersi

                                    </div>


                                </div>


                            </div>

                        </td>



                        <!-- DERS KODU -->

                        <td>

                            <span class="badge">

                                <?php

                                echo htmlspecialchars(
                                    $ders["dersKodu"]
                                );

                                ?>

                            </span>

                        </td>



                        <!-- AKADEMİSYEN -->

                        <td>


                            <?php if (!empty($ders["akademisyen"])): ?>


                                <?php

                                echo htmlspecialchars(
                                    $ders["akademisyen"]
                                );

                                ?>


                            <?php else: ?>


                                <span class="badge badge-empty">
                                    Atanmadı
                                </span>


                            <?php endif; ?>


                        </td>



                        <!-- İŞLEMLER -->

                        <td>


                            <div class="actions">


                                <a
                                    href="/student_project/admin/courses/ogrenciler.php?dersKodu=<?php
                                    echo urlencode(
                                        $ders["dersKodu"]
                                    );
                                    ?>"
                                    class="btn btn-students"
                                >
                                    Öğrenciler
                                </a>


                                <a
                                    href="/student_project/admin/courses/ders_duzenle.php?dersKodu=<?php
                                    echo urlencode(
                                        $ders["dersKodu"]
                                    );
                                    ?>"
                                    class="btn btn-edit"
                                >
                                    Düzenle
                                </a>


                                <a
                                    href="/student_project/admin/courses/ders_sil.php?dersKodu=<?php
                                    echo urlencode(
                                        $ders["dersKodu"]
                                    );
                                    ?>"
                                    class="btn btn-delete"

                                    onclick="return confirm(
                                        'Bu dersi silmek istediğinize emin misiniz?'
                                    );"
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


            <div class="empty-icon">
                📚
            </div>


            <h3>
                Ders bulunamadı
            </h3>


            <p>

                <?php if ($arama !== ""): ?>

                    "<?php
                    echo htmlspecialchars($arama);
                    ?>"
                    aramasıyla eşleşen ders bulunamadı.

                <?php else: ?>

                    Sistemde henüz tanımlı ders bulunmuyor.

                <?php endif; ?>

            </p>


        </div>


    <?php endif; ?>


</div>


</main>

</div>


</body>

</html>