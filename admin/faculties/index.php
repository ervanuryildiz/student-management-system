<?php

session_start();

// ==========================================
// YETKİ KONTROLÜ
// ==========================================
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


// ==========================================
// ARAMA
// ==========================================
$arama = trim($_GET["arama"] ?? "");


// ==========================================
// FAKÜLTELER
// ==========================================
if ($arama !== "") {

    $sql = "
        SELECT *
        FROM fakulte
        WHERE `fakülte` LIKE :arama
        ORDER BY `fakülte` ASC
    ";

    $stmt = $baglanti->prepare($sql);

    $stmt->execute([
        "arama" => "%" . $arama . "%"
    ]);

} else {

    $sql = "
        SELECT *
        FROM fakulte
        ORDER BY `fakülte` ASC
    ";

    $stmt = $baglanti->prepare($sql);
    $stmt->execute();
}

$fakulteler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// BÖLÜMLER
// ==========================================
if ($arama !== "") {

    $sqlBolum = "
        SELECT *
        FROM bolum
        WHERE `bölüm` LIKE :arama1
           OR `fakülte` LIKE :arama2
        ORDER BY `fakülte` ASC, `bölüm` ASC
    ";

    $stmtBolum = $baglanti->prepare($sqlBolum);

    $aramaDegeri = "%" . $arama . "%";

    $stmtBolum->execute([
        "arama1" => $aramaDegeri,
        "arama2" => $aramaDegeri
    ]);

} else {

    $sqlBolum = "
        SELECT *
        FROM bolum
        ORDER BY `fakülte` ASC, `bölüm` ASC
    ";

    $stmtBolum = $baglanti->prepare($sqlBolum);
    $stmtBolum->execute();
}

$bolumler = $stmtBolum->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// İSTATİSTİKLER
// ==========================================
$stmtToplamFakulte = $baglanti->query("
    SELECT COUNT(*) FROM fakulte
");

$toplamFakulte = (int)$stmtToplamFakulte->fetchColumn();


$stmtToplamBolum = $baglanti->query("
    SELECT COUNT(*) FROM bolum
");

$toplamBolum = (int)$stmtToplamBolum->fetchColumn();


// Bölümü bulunan fakülte sayısı
$stmtAktifFakulte = $baglanti->query("
    SELECT COUNT(DISTINCT `fakülte`)
    FROM bolum
");

$bolumuOlanFakulte = (int)$stmtAktifFakulte->fetchColumn();


$bolumuOlmayanFakulte =
    max(0, $toplamFakulte - $bolumuOlanFakulte);


$adminAdSoyad =
    $_SESSION["ad_soyad"] ?? "Admin";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Fakülte ve Bölüm İşlemleri</title>

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


/* ==============================
   SIDEBAR
============================== */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

    width: 250px;
    height: 100vh;

    background: #0f172a;

    padding: 25px 18px;

    color: white;

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

    background: #2563eb;

    border-radius: 10px;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 22px;
}


.logo h2 {

    font-size: 16px;

    margin-bottom: 3px;
}


.logo span {

    color: #94a3b8;

    font-size: 11px;
}


.menu-title {

    color: #64748b;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin: 20px 10px 10px;
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

    background: #dc2626;

    color: white;

    text-align: center;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}


.logout-sidebar:hover {

    background: #b91c1c;
}


/* ==============================
   MAIN
============================== */

.main {

    margin-left: 250px;

    min-height: 100vh;
}


/* ==============================
   HEADER
============================== */

.header {

    height: 72px;

    background: white;

    border-bottom: 1px solid #e2e8f0;

    display: flex;

    justify-content: space-between;

    align-items: center;

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

    color: #94a3b8;

    font-size: 11px;
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

    font-weight: 700;
}


/* ==============================
   CONTENT
============================== */

.content {

    max-width: 1450px;

    margin: auto;

    padding: 35px;
}


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


.page-buttons {

    display: flex;

    gap: 10px;
}


.add-btn {

    display: inline-flex;

    align-items: center;

    padding: 11px 15px;

    border-radius: 8px;

    color: white;

    font-size: 13px;

    font-weight: 600;
}


.add-faculty {

    background: #2563eb;
}


.add-faculty:hover {

    background: #1d4ed8;
}


.add-department {

    background: #7c3aed;
}


.add-department:hover {

    background: #6d28d9;
}


/* ==============================
   İSTATİSTİKLER
============================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 17px;

    margin-bottom: 28px;
}


.stat-card {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 18px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.stat-label {

    color: #64748b;

    font-size: 12px;
}


.stat-number {

    display: block;

    margin-top: 5px;

    font-size: 25px;

    font-weight: 700;

    color: #0f172a;
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


/* ==============================
   MESAJLAR
============================== */

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


/* ==============================
   ARAMA
============================== */

.toolbar {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 17px;

    margin-bottom: 22px;
}


.search-form {

    display: flex;

    gap: 9px;

    max-width: 700px;
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

    border-radius: 8px;

    padding: 0 18px;

    background: #0f172a;

    color: white;

    font-weight: 600;

    cursor: pointer;
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


/* ==============================
   İKİ KOLON
============================== */

.management-grid {

    display: grid;

    grid-template-columns:
        minmax(350px, .8fr)
        minmax(500px, 1.2fr);

    gap: 22px;

    align-items: start;
}


/* ==============================
   TABLO KARTI
============================== */

.table-card {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    overflow: hidden;
}


.table-card-header {

    padding: 18px 20px;

    border-bottom: 1px solid #e2e8f0;

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.table-card-header h3 {

    color: #0f172a;

    font-size: 15px;

    margin-bottom: 4px;
}


.table-card-header p {

    color: #94a3b8;

    font-size: 12px;
}


.count-badge {

    padding: 5px 9px;

    border-radius: 20px;

    background: #eff6ff;

    color: #2563eb;

    font-size: 11px;

    font-weight: 700;
}


.table-wrapper {

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;
}


th {

    padding: 13px 18px;

    background: #f8fafc;

    border-bottom: 1px solid #e2e8f0;

    color: #64748b;

    text-align: left;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .4px;
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


.item {

    display: flex;

    align-items: center;

    gap: 11px;
}


.item-icon {

    width: 38px;
    height: 38px;

    flex-shrink: 0;

    border-radius: 9px;

    background: #eff6ff;

    display: flex;

    align-items: center;
    justify-content: center;
}


.item-name {

    font-weight: 600;

    color: #0f172a;
}


.faculty-badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: #ede9fe;

    color: #6d28d9;

    font-size: 11px;

    font-weight: 600;
}


/* ==============================
   İŞLEM BUTONLARI
============================== */

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}


.btn {

    display: inline-block;

    padding: 7px 10px;

    border-radius: 6px;

    font-size: 11px;

    font-weight: 600;
}


.btn-edit {

    background: #e0f2fe;

    color: #0369a1;
}


.btn-delete {

    background: #fee2e2;

    color: #b91c1c;
}


/* ==============================
   EMPTY
============================== */

.empty {

    padding: 40px 20px;

    text-align: center;

    color: #94a3b8;

    font-size: 13px;
}


/* ==============================
   BACK
============================== */

.back-area {

    margin-top: 25px;
}


.back-btn {

    color: #475569;

    font-size: 13px;

    font-weight: 600;
}


.back-btn:hover {

    color: #2563eb;
}


/* ==============================
   RESPONSIVE
============================== */

@media(max-width:1100px) {

    .management-grid {

        grid-template-columns: 1fr;
    }


    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media(max-width:800px) {

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


    .main {

        margin-left: 75px;
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
}


@media(max-width:650px) {

    .content {

        padding: 22px 15px;
    }


    .header {

        padding: 0 18px;
    }


    .admin-text {

        display: none;
    }


    .page-header {

        flex-direction: column;
    }


    .page-buttons {

        width: 100%;

        flex-direction: column;
    }


    .add-btn {

        justify-content: center;
    }


    .stats {

        grid-template-columns: 1fr;
    }


    .search-form {

        flex-direction: column;
    }


    .search-input,
    .search-btn,
    .clear-btn {

        min-height: 42px;
    }


    .clear-btn {

        justify-content: center;
    }
}

</style>

</head>


<body>


<!-- ==============================
     SIDEBAR
============================== -->

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


<a
    href="/student_project/admin/faculties/index.php"
    class="active"
>

    <span class="menu-icon">🏫</span>

    <span>Fakülte / Bölüm</span>

</a>


<a href="/student_project/admin/courses/index.php">

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



<!-- ==============================
     MAIN
============================== -->

<div class="main">


<header class="header">


<div class="header-title">

    <h2>
        Fakülte / Bölüm Yönetimi
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



<main class="content">


<!-- ==============================
     BAŞLIK
============================== -->

<div class="page-header">


<div>

    <h1>
        Fakülte ve Bölüm İşlemleri
    </h1>

    <p>
        Üniversitenin fakülte ve bölüm
        yapısını buradan yönetebilirsiniz.
    </p>

</div>


<div class="page-buttons">

    <a
        href="/student_project/admin/faculties/fakulte_ekle.php"
        class="add-btn add-faculty"
    >
        ＋ Fakülte Ekle
    </a>


    <a
        href="/student_project/admin/faculties/bolum_ekle.php"
        class="add-btn add-department"
    >
        ＋ Bölüm Ekle
    </a>

</div>


</div>



<!-- ==============================
     MESAJLAR
============================== -->

<?php if (isset($_GET["durum"])): ?>


<?php if ($_GET["durum"] === "fakulte_eklendi"): ?>

<div class="message success">
    Fakülte başarıyla eklendi.
</div>


<?php elseif ($_GET["durum"] === "bolum_eklendi"): ?>

<div class="message success">
    Bölüm başarıyla eklendi.
</div>


<?php elseif ($_GET["durum"] === "fakulte_silindi"): ?>

<div class="message success">
    Fakülte başarıyla silindi.
</div>


<?php elseif ($_GET["durum"] === "bolum_silindi"): ?>

<div class="message success">
    Bölüm başarıyla silindi.
</div>


<?php elseif ($_GET["durum"] === "guncellendi"): ?>

<div class="message success">
    Kayıt başarıyla güncellendi.
</div>


<?php elseif ($_GET["durum"] === "hata"): ?>

<div class="message error">
    İşlem gerçekleştirilemedi.
</div>


<?php endif; ?>


<?php endif; ?>



<!-- ==============================
     İSTATİSTİKLER
============================== -->

<div class="stats">


<div class="stat-card">

    <div>

        <span class="stat-label">
            Toplam Fakülte
        </span>

        <span class="stat-number">
            <?php echo $toplamFakulte; ?>
        </span>

    </div>

    <div class="stat-icon">
        🏫
    </div>

</div>


<div class="stat-card">

    <div>

        <span class="stat-label">
            Toplam Bölüm
        </span>

        <span class="stat-number">
            <?php echo $toplamBolum; ?>
        </span>

    </div>

    <div class="stat-icon">
        🎓
    </div>

</div>


<div class="stat-card">

    <div>

        <span class="stat-label">
            Bölümü Olan Fakülte
        </span>

        <span class="stat-number">
            <?php echo $bolumuOlanFakulte; ?>
        </span>

    </div>

    <div class="stat-icon">
        ✓
    </div>

</div>


<div class="stat-card">

    <div>

        <span class="stat-label">
            Bölümü Olmayan Fakülte
        </span>

        <span class="stat-number">
            <?php echo $bolumuOlmayanFakulte; ?>
        </span>

    </div>

    <div class="stat-icon">
        !
    </div>

</div>


</div>



<!-- ==============================
     ARAMA
============================== -->

<div class="toolbar">


<form
    method="GET"
    class="search-form"
>


<input
    type="text"
    name="arama"
    class="search-input"
    placeholder="Fakülte veya bölüm ara..."
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
    href="/student_project/admin/faculties/index.php"
    class="clear-btn"
>
    Temizle
</a>

<?php endif; ?>


</form>


</div>



<!-- ==============================
     TABLOLAR
============================== -->

<div class="management-grid">



<!-- ==============================
     FAKÜLTELER
============================== -->

<div class="table-card">


<div class="table-card-header">

    <div>

        <h3>
            Fakülteler
        </h3>

        <p>
            Sistemde kayıtlı fakülteler
        </p>

    </div>


    <span class="count-badge">

        <?php
        echo count($fakulteler);
        ?>

    </span>

</div>



<?php if (count($fakulteler) > 0): ?>


<div class="table-wrapper">


<table>


<thead>

<tr>

    <th>Fakülte</th>

    <th>İşlem</th>

</tr>

</thead>


<tbody>


<?php foreach ($fakulteler as $fakulte): ?>


<tr>


<td>


<div class="item">

    <div class="item-icon">
        🏫
    </div>


    <div class="item-name">

        <?php

        echo htmlspecialchars(
            $fakulte["fakülte"]
        );

        ?>

    </div>

</div>


</td>



<td>


<div class="actions">


<a
    href="fakulte_duzenle.php?fakulte=<?php
    echo urlencode(
        $fakulte["fakülte"]
    );
    ?>"
    class="btn btn-edit"
>
    Düzenle
</a>


<a
    href="fakulte_sil.php?fakulte=<?php
    echo urlencode(
        $fakulte["fakülte"]
    );
    ?>"
    class="btn btn-delete"

    onclick="return confirm(
        'Bu fakülteyi silmek istediğinize emin misiniz?'
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

    Fakülte bulunamadı.

</div>


<?php endif; ?>


</div>



<!-- ==============================
     BÖLÜMLER
============================== -->

<div class="table-card">


<div class="table-card-header">


<div>

    <h3>
        Bölümler
    </h3>

    <p>
        Fakültelere bağlı bölümler
    </p>

</div>


<span class="count-badge">

    <?php
    echo count($bolumler);
    ?>

</span>


</div>



<?php if (count($bolumler) > 0): ?>


<div class="table-wrapper">


<table>


<thead>


<tr>

    <th>Bölüm</th>

    <th>Fakülte</th>

    <th>İşlem</th>

</tr>


</thead>


<tbody>


<?php foreach ($bolumler as $bolum): ?>


<tr>


<td>


<div class="item">

    <div class="item-icon">
        🎓
    </div>


    <div class="item-name">

        <?php

        echo htmlspecialchars(
            $bolum["bölüm"]
        );

        ?>

    </div>

</div>


</td>



<td>


<span class="faculty-badge">

<?php

echo htmlspecialchars(
    $bolum["fakülte"]
);

?>

</span>


</td>



<td>


<div class="actions">


<a
    href="bolum_duzenle.php?bolum=<?php
    echo urlencode(
        $bolum["bölüm"]
    );
    ?>&fakulte=<?php
    echo urlencode(
        $bolum["fakülte"]
    );
    ?>"
    class="btn btn-edit"
>
    Düzenle
</a>


<a
    href="bolum_sil.php?bolum=<?php
    echo urlencode(
        $bolum["bölüm"]
    );
    ?>&fakulte=<?php
    echo urlencode(
        $bolum["fakülte"]
    );
    ?>"
    class="btn btn-delete"

    onclick="return confirm(
        'Bu bölümü silmek istediğinize emin misiniz?'
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

    Bölüm bulunamadı.

</div>


<?php endif; ?>


</div>


</div>



<!-- ==============================
     GERİ
============================== -->

<div class="back-area">

<a
    href="/student_project/admin/dashboard.php"
    class="back-btn"
>
    ← Admin Paneline Dön
</a>

</div>


</main>


</div>


</body>

</html>