<?php

require_once __DIR__ . '/../../includes/auth.php';
rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$akademisyen = $_SESSION["kullanici"] ?? "";


// ==========================================
// GELEN ADMIN DUYURULARI
// ==========================================

$stmt = $baglanti->prepare("
    SELECT *
    FROM duyuru

    WHERE yayinlayanRol = 'admin'
    AND hedefKitle = 'akademisyen'

    ORDER BY olusturmaTarihi DESC
");

$stmt->execute();

$gelenDuyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// AKADEMİSYENİN YAYINLADIĞI DUYURULAR
// ==========================================

$stmt = $baglanti->prepare("
    SELECT *
    FROM duyuru

    WHERE yayinlayanRol = 'akademisyen'
    AND yayinlayan = :yayinlayan

    ORDER BY olusturmaTarihi DESC
");

$stmt->execute([
    "yayinlayan" => $akademisyen
]);

$duyurularim = $stmt->fetchAll(PDO::FETCH_ASSOC);


$tab = $_GET["tab"] ?? "gelen";

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Duyurular</title>

<link rel="stylesheet"
      href="/student_project/assets/css/style.css">

<style>

.announcement-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
}

.tabs {
    display: flex;
    gap: 5px;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 8px;
}

.tab {
    padding: 9px 14px;
    border-radius: 6px;
    color: #64748b;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.tab.active {
    background: white;
    color: #0f172a;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.btn-add {
    padding: 10px 15px;
    background: #2563eb;
    color: white;
    border-radius: 7px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.announcement-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.announcement-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 19px 21px;
}

.announcement-head {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 10px;
}

.announcement-title {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.announcement-title h3 {
    margin: 0;
    color: #0f172a;
    font-size: 15px;
}

.type {
    padding: 4px 8px;
    border-radius: 20px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 700;
}

.date {
    color: #94a3b8;
    font-size: 11px;
    white-space: nowrap;
}

.announcement-content {
    color: #64748b;
    font-size: 13px;
    line-height: 1.7;
}

.meta {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    color: #94a3b8;
    font-size: 11px;
}

.actions {
    display: flex;
    gap: 7px;
    margin-top: 14px;
}

.btn-edit,
.btn-delete {
    padding: 7px 11px;

    border: none;

    border-radius: 6px;

    background: #fee2e2;

    color: #b91c1c;

    cursor: pointer;

    font-family: inherit;

    font-size: 11px;

    font-weight: 600;
}

.btn-edit {
    background: #eff6ff;
    color: #1d4ed8;
}

.btn-delete {
    background: #fee2e2;
    color: #b91c1c;
}

.empty {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 45px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

</style>

</head>

<body>

<?php
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="main">

<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<main class="content">

<div class="page-header">

    <h1>Duyurular</h1>

    <p>
        Yönetimden gelen duyuruları görüntüleyebilir
        ve öğrencilerinize duyuru yayınlayabilirsiniz.
    </p>

</div>


<div class="announcement-toolbar">

    <div class="tabs">

        <a
            href="?tab=gelen"
            class="tab <?php echo $tab === "gelen" ? "active" : ""; ?>"
        >
            Gelen Duyurular
            (<?php echo count($gelenDuyurular); ?>)
        </a>

        <a
            href="?tab=yayinlanan"
            class="tab <?php echo $tab === "yayinlanan" ? "active" : ""; ?>"
        >
            Yayınladıklarım
            (<?php echo count($duyurularim); ?>)
        </a>

    </div>


    <a
        href="add.php"
        class="btn-add"
    >
        + Yeni Duyuru
    </a>

</div>


<?php if ($tab === "yayinlanan"): ?>


    <?php if (count($duyurularim) > 0): ?>

        <div class="announcement-list">

        <?php foreach ($duyurularim as $duyuru): ?>

            <div class="announcement-item">

                <div class="announcement-head">

                    <div class="announcement-title">

                        <h3>
                            <?php echo htmlspecialchars($duyuru["baslik"]); ?>
                        </h3>

                        <span class="type">
                            <?php echo htmlspecialchars($duyuru["duyuruTuru"]); ?>
                        </span>

                    </div>

                    <span class="date">

                        <?php
                        echo date(
                            "d.m.Y H:i",
                            strtotime($duyuru["olusturmaTarihi"])
                        );
                        ?>

                    </span>

                </div>


                <div class="announcement-content">

                    <?php
                    echo nl2br(
                        htmlspecialchars($duyuru["icerik"])
                    );
                    ?>

                </div>


                <div class="meta">

                    Hedef: Öğrenciler

                    <?php if (!empty($duyuru["dersKodu"])): ?>

                        • Ders:
                        <?php
                        echo htmlspecialchars($duyuru["dersKodu"]);
                        ?>

                    <?php endif; ?>

                </div>


                <div class="actions">

                    <a
                        href="edit.php?id=<?php echo (int)$duyuru["duyuruId"]; ?>"
                        class="btn-edit"
                    >
                        Düzenle
                    </a>

                    <form
    action="delete.php"
    method="POST"
    style="display:inline;"
    onsubmit="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');"
>

    <input
        type="hidden"
        name="id"
        value="<?php echo (int)$duyuru["duyuruId"]; ?>"
    >

    <button
        type="submit"
        class="btn-delete"
    >
        Sil
    </button>

</form>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">
            Henüz yayınladığınız bir duyuru bulunmuyor.
        </div>

    <?php endif; ?>


<?php else: ?>


    <?php if (count($gelenDuyurular) > 0): ?>

        <div class="announcement-list">

        <?php foreach ($gelenDuyurular as $duyuru): ?>

            <div class="announcement-item">

                <div class="announcement-head">

                    <div class="announcement-title">

                        <h3>
                            <?php echo htmlspecialchars($duyuru["baslik"]); ?>
                        </h3>

                        <span class="type">
                            <?php echo htmlspecialchars($duyuru["duyuruTuru"]); ?>
                        </span>

                    </div>

                    <span class="date">

                        <?php
                        echo date(
                            "d.m.Y H:i",
                            strtotime($duyuru["olusturmaTarihi"])
                        );
                        ?>

                    </span>

                </div>


                <div class="announcement-content">

                    <?php
                    echo nl2br(
                        htmlspecialchars($duyuru["icerik"])
                    );
                    ?>

                </div>


                <div class="meta">

                    Yönetim tarafından yayınlandı

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">
            Yönetim tarafından gönderilmiş yeni bir duyuru bulunmuyor.
        </div>

    <?php endif; ?>


<?php endif; ?>


</main>

</div>

</body>
</html>