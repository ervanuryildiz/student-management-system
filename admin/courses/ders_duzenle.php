<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$dersKodu =
    trim($_GET["dersKodu"] ?? "");


if ($dersKodu === "") {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


// DERS

$stmt = $baglanti->prepare("
    SELECT *
    FROM ders
    WHERE dersKodu = :dersKodu
");

$stmt->execute([
    "dersKodu" => $dersKodu
]);

$ders = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$ders) {

    header(
        "Location: index.php?durum=hata"
    );

    exit;
}


// AKADEMİSYENLER

$stmt = $baglanti->prepare("
    SELECT
        kullaniciAdi,
        ad,
        soyad

    FROM admin

    WHERE unvan = 'akademisyen'

    ORDER BY ad, soyad
");

$stmt->execute();

$akademisyenler =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


$hata = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dersAdi =
        trim($_POST["dersAdi"] ?? "");

    $akademisyen =
        trim($_POST["akademisyen"] ?? "");


    if ($dersAdi === "") {

        $hata =
            "Ders adı boş bırakılamaz.";

    } else {

        if ($akademisyen !== "") {

            $kontrol =
                $baglanti->prepare("
                    SELECT COUNT(*)

                    FROM admin

                    WHERE kullaniciAdi = :kullaniciAdi
                    AND unvan = 'akademisyen'
                ");

            $kontrol->execute([
                "kullaniciAdi" => $akademisyen
            ]);


            if ($kontrol->fetchColumn() == 0) {

                $hata =
                    "Seçilen akademisyen geçersiz.";
            }
        }


        if ($hata === "") {

            $stmt = $baglanti->prepare("
                UPDATE ders

                SET
                    dersAdi = :dersAdi,
                    akademisyen = :akademisyen

                WHERE dersKodu = :dersKodu
            ");

            $stmt->execute([
                "dersAdi" => $dersAdi,

                "akademisyen" =>
                    $akademisyen !== ""
                    ? $akademisyen
                    : null,

                "dersKodu" => $dersKodu
            ]);


            header(
                "Location: index.php?durum=guncellendi"
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
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ders Düzenle</title>

<style>

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f7fb;
}

.header {
    height: 70px;
    background: #0f172a;
    color: white;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 35px;
}

.container {
    max-width: 680px;
    margin: 50px auto;
    padding: 0 20px;
}

.card {
    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 14px;

    padding: 30px;
}

h1 {
    margin: 0 0 7px;
}

.description {
    color: #64748b;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 19px;
}

label {
    display: block;

    margin-bottom: 7px;

    font-weight: 600;
    font-size: 13px;
}

input,
select {
    width: 100%;

    height: 44px;

    border: 1px solid #cbd5e1;

    border-radius: 8px;

    padding: 0 12px;

    background: white;
}

.readonly {
    background: #f1f5f9;
    color: #64748b;
}

.actions {
    display: flex;
    gap: 10px;

    margin-top: 25px;
}

.btn {
    padding: 11px 17px;

    border: none;

    border-radius: 8px;

    font-weight: 600;

    text-decoration: none;

    cursor: pointer;
}

.save {
    background: #2563eb;
    color: white;
}

.back {
    background: #e2e8f0;
    color: #475569;
}

.error {
    background: #fee2e2;
    color: #991b1b;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="header">

<strong>
🎓 Öğrenci Takip Sistemi
</strong>

<span>
<?php
echo htmlspecialchars(
    $_SESSION["ad_soyad"] ?? ""
);
?>
</span>

</div>


<div class="container">

<div class="card">

<h1>📚 Ders Düzenle</h1>

<p class="description">
Ders bilgilerini ve akademisyen atamasını değiştirebilirsiniz.
</p>


<?php if ($hata !== ""): ?>

<div class="error">
<?php echo htmlspecialchars($hata); ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>Ders Kodu</label>

<input
    type="text"
    class="readonly"
    value="<?php
    echo htmlspecialchars(
        $ders["dersKodu"]
    );
    ?>"
    readonly
>

</div>


<div class="form-group">

<label>Ders Adı</label>

<input
    type="text"
    name="dersAdi"
    value="<?php
    echo htmlspecialchars(
        $_POST["dersAdi"]
        ?? $ders["dersAdi"]
    );
    ?>"
    required
>

</div>


<div class="form-group">

<label>Akademisyen</label>

<?php

$aktifAkademisyen =
    $_POST["akademisyen"]
    ?? $ders["akademisyen"];

?>

<select name="akademisyen">

<option value="">
Akademisyen atanmasın
</option>


<?php foreach ($akademisyenler as $a): ?>

<option
    value="<?php
    echo htmlspecialchars(
        $a["kullaniciAdi"]
    );
    ?>"

    <?php
    echo $aktifAkademisyen === $a["kullaniciAdi"]
        ? "selected"
        : "";
    ?>
>

<?php

echo htmlspecialchars(
    $a["ad"]
    . " "
    . $a["soyad"]
    . " ("
    . $a["kullaniciAdi"]
    . ")"
);

?>

</option>

<?php endforeach; ?>


</select>

</div>


<div class="actions">

<button
    type="submit"
    class="btn save"
>
Değişiklikleri Kaydet
</button>

<a
    href="index.php"
    class="btn back"
>
Vazgeç
</a>

</div>


</form>

</div>

</div>

</body>

</html>