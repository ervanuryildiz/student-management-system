<?php

require_once __DIR__ . '/../../includes/auth.php';
rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$akademisyen = $_SESSION["kullanici"] ?? "";

$dersKodu = trim($_GET["dersKodu"] ?? "");
$ogrenciNo = trim($_GET["ogrenciNo"] ?? "");

$hata = "";
$basarili = "";


// ==========================================
// YETKİ + KAYIT KONTROLÜ
// ==========================================

$stmt = $baglanti->prepare("
    SELECT
        o.ogrenciNo,
        o.ad,
        o.soyad,
        d.dersKodu,
        d.dersAdi

    FROM ogrenci_ders od

    INNER JOIN ogrenci o
        ON o.ogrenciNo = od.ogrenciNo

    INNER JOIN ders d
        ON d.dersKodu = od.dersKodu

    WHERE od.ogrenciNo = :ogrenciNo
    AND od.dersKodu = :dersKodu
    AND d.akademisyen = :akademisyen

    LIMIT 1
");

$stmt->execute([
    "ogrenciNo" => $ogrenciNo,
    "dersKodu" => $dersKodu,
    "akademisyen" => $akademisyen
]);

$kayit = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$kayit) {
    header("Location: index.php");
    exit;
}


// ==========================================
// MEVCUT NOT
// ==========================================

$stmt = $baglanti->prepare("
    SELECT vize, final
    FROM notlar
    WHERE ogrenciNo = :ogrenciNo
    AND dersKodu = :dersKodu
    LIMIT 1
");

$stmt->execute([
    "ogrenciNo" => $ogrenciNo,
    "dersKodu" => $dersKodu
]);

$not = $stmt->fetch(PDO::FETCH_ASSOC);

$vize = $not["vize"] ?? "";
$final = $not["final"] ?? "";


// ==========================================
// KAYDET
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $vize = trim($_POST["vize"] ?? "");
    $final = trim($_POST["final"] ?? "");


    if (
        ($vize !== "" && (!is_numeric($vize) || $vize < 0 || $vize > 100)) ||
        ($final !== "" && (!is_numeric($final) || $final < 0 || $final > 100))
    ) {

        $hata = "Notlar 0 ile 100 arasında olmalıdır.";

    } else {

        $vizeDb = $vize === "" ? null : $vize;
        $finalDb = $final === "" ? null : $final;


        if ($not) {

            $stmt = $baglanti->prepare("
                UPDATE notlar

                SET
                    vize = :vize,
                    final = :final

                WHERE ogrenciNo = :ogrenciNo
                AND dersKodu = :dersKodu
            ");

        } else {

            $stmt = $baglanti->prepare("
                INSERT INTO notlar
                (
                    ogrenciNo,
                    dersKodu,
                    vize,
                    final
                )

                VALUES
                (
                    :ogrenciNo,
                    :dersKodu,
                    :vize,
                    :final
                )
            ");
        }


        $stmt->execute([
            "ogrenciNo" => $ogrenciNo,
            "dersKodu" => $dersKodu,
            "vize" => $vizeDb,
            "final" => $finalDb
        ]);


        $basarili = "Notlar başarıyla kaydedildi.";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Not Düzenle</title>

<link
    rel="stylesheet"
    href="/student_project/assets/css/style.css"
>

<style>

.form-card {
    max-width: 700px;
    padding: 25px;
}

.info {
    padding: 15px;
    margin-bottom: 25px;

    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.info strong {
    display: block;
    margin-bottom: 5px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 11px 13px;

    border: 1px solid #cbd5e1;
    border-radius: 7px;

    outline: none;
}

input:focus {
    border-color: #2563eb;
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 7px;

    cursor: pointer;

    font-weight: 600;
}

.save {
    background: #2563eb;
    color: white;
}

.cancel {
    background: #f1f5f9;
    color: #475569;
}

.alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 7px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.success {
    background: #dcfce7;
    color: #166534;
}

</style>

</head>

<body>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="main">

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="content">

<div class="page-header">

<h1>Not Düzenle</h1>

<p>
    Öğrencinin vize ve final notlarını düzenleyebilirsiniz.
</p>

</div>


<div class="card form-card">

<div class="info">

<strong>
<?php
echo htmlspecialchars(
    $kayit["ad"] . " " . $kayit["soyad"]
);
?>
</strong>

<?php echo htmlspecialchars($kayit["ogrenciNo"]); ?>

&nbsp;•&nbsp;

<?php echo htmlspecialchars($kayit["dersAdi"]); ?>

</div>


<?php if ($hata): ?>

<div class="alert error">
<?php echo htmlspecialchars($hata); ?>
</div>

<?php endif; ?>


<?php if ($basarili): ?>

<div class="alert success">
<?php echo htmlspecialchars($basarili); ?>
</div>

<?php endif; ?>


<form method="POST">

<div class="form-row">

<div class="form-group">

<label>Vize</label>

<input
    type="number"
    name="vize"
    min="0"
    max="100"
    step="0.01"
    value="<?php echo htmlspecialchars($vize); ?>"
>

</div>


<div class="form-group">

<label>Final</label>

<input
    type="number"
    name="final"
    min="0"
    max="100"
    step="0.01"
    value="<?php echo htmlspecialchars($final); ?>"
>

</div>

</div>


<div class="actions">

<a
    class="btn cancel"
    href="index.php?ders=<?php echo urlencode($dersKodu); ?>"
>
    İptal
</a>

<button
    type="submit"
    class="btn save"
>
    Notları Kaydet
</button>

</div>

</form>

</div>

</main>
</div>

</body>
</html>