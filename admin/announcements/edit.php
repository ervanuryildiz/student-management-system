<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}


$stmt = $baglanti->prepare("
    SELECT *
    FROM duyuru
    WHERE duyuruId = :id
");

$stmt->execute([
    "id" => $id
]);

$duyuru = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$duyuru) {
    header("Location: index.php");
    exit;
}


$stmt = $baglanti->query("
    SELECT dersKodu, dersAdi
    FROM ders
    ORDER BY dersAdi
");

$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hata = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $baslik = trim($_POST["baslik"] ?? "");
    $icerik = trim($_POST["icerik"] ?? "");
    $duyuruTuru = $_POST["duyuruTuru"] ?? "";
    $dersKodu = trim($_POST["dersKodu"] ?? "");


    if ($baslik === "" || $icerik === "") {

        $hata = "Başlık ve içerik zorunludur.";

    } elseif (!in_array($duyuruTuru, ["genel", "ders"], true)) {

        $hata = "Geçersiz duyuru türü.";

    } elseif ($duyuruTuru === "ders" && $dersKodu === "") {

        $hata = "Ders seçmelisiniz.";

    } else {

        if ($duyuruTuru === "genel") {
            $dersKodu = null;
        }


        if ($dersKodu !== null) {

            $kontrol = $baglanti->prepare("
                SELECT COUNT(*)
                FROM ders
                WHERE dersKodu = :dersKodu
            ");

            $kontrol->execute([
                "dersKodu" => $dersKodu
            ]);

            if ((int)$kontrol->fetchColumn() === 0) {
                $hata = "Seçilen ders bulunamadı.";
            }
        }


        if ($hata === "") {

            $stmt = $baglanti->prepare("
                UPDATE duyuru

                SET
                    baslik = :baslik,
                    icerik = :icerik,
                    duyuruTuru = :duyuruTuru,
                    dersKodu = :dersKodu,
                    guncellemeTarihi = NOW()

                WHERE duyuruId = :id
            ");

            $stmt->execute([

                "baslik" => $baslik,

                "icerik" => $icerik,

                "duyuruTuru" => $duyuruTuru,

                "dersKodu" => $dersKodu,

                "id" => $id

            ]);

            header("Location: index.php?durum=guncellendi");
            exit;
        }
    }

} else {

    $baslik = $duyuru["baslik"];
    $icerik = $duyuru["icerik"];
    $duyuruTuru = $duyuru["duyuruTuru"];
    $dersKodu = $duyuru["dersKodu"] ?? "";
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Duyuru Düzenle</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f7fb;
    color: #1e293b;
}

.header {
    background: #0f172a;
    color: white;
    padding: 20px 40px;
}

.container {
    max-width: 850px;
    margin: 35px auto;
    padding: 0 20px;
}

.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    padding: 22px 25px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h1 {
    margin: 0 0 6px;
}

.card-header p {
    margin: 0;
    color: #64748b;
}

.card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: 600;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-family: inherit;
}

textarea {
    min-height: 170px;
    resize: vertical;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    padding: 10px 17px;
    border: none;
    border-radius: 7px;
    text-decoration: none;
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

<header class="header">
    <strong>📢 Duyuru Düzenle</strong>
</header>


<main class="container">

<div class="card">

    <div class="card-header">

        <h1>Duyuruyu Düzenle</h1>

        <p>
            Duyurunun başlık, içerik veya hedef bilgisini değiştirebilirsiniz.
        </p>

    </div>


    <div class="card-body">

        <?php if ($hata !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($hata); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Başlık</label>

                <input
                    type="text"
                    name="baslik"
                    value="<?php echo htmlspecialchars($baslik); ?>"
                    maxlength="150"
                    required
                >

            </div>


            <div class="form-group">

                <label>Duyuru Türü</label>

                <select
                    name="duyuruTuru"
                    id="duyuruTuru"
                    onchange="turDegisti()"
                >

                    <option
                        value="genel"
                        <?php echo $duyuruTuru === "genel" ? "selected" : ""; ?>
                    >
                        Genel Duyuru
                    </option>

                    <option
                        value="ders"
                        <?php echo $duyuruTuru === "ders" ? "selected" : ""; ?>
                    >
                        Ders Duyurusu
                    </option>

                </select>

            </div>


            <div
                class="form-group"
                id="dersAlani"
            >

                <label>Ders</label>

                <select name="dersKodu">

                    <option value="">
                        Ders Seçiniz
                    </option>

                    <?php foreach ($dersler as $ders): ?>

                        <option
                            value="<?php
                            echo htmlspecialchars($ders["dersKodu"]);
                            ?>"
                            <?php
                            echo $dersKodu === $ders["dersKodu"]
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

                <label>İçerik</label>

                <textarea
                    name="icerik"
                    maxlength="5000"
                    required
                ><?php echo htmlspecialchars($icerik); ?></textarea>

            </div>


            <div class="actions">

                <a
                    href="index.php"
                    class="btn cancel"
                >
                    İptal
                </a>

                <button
                    type="submit"
                    class="btn save"
                >
                    Değişiklikleri Kaydet
                </button>

            </div>

        </form>

    </div>

</div>

</main>


<script>

function turDegisti() {

    const tur =
        document.getElementById("duyuruTuru").value;

    document.getElementById("dersAlani").style.display =
        tur === "ders"
            ? "block"
            : "none";
}

turDegisti();

</script>

</body>
</html>