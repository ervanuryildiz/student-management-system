<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


// AKADEMİSYENLER

$stmt = $baglanti->prepare("
    SELECT
        kullaniciAdi,
        ad,
        soyad

    FROM admin

    WHERE unvan = 'akademisyen'

    ORDER BY ad ASC, soyad ASC
");

$stmt->execute();

$akademisyenler =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


$hata = "";

$dersKodu = "";
$dersAdi = "";
$akademisyen = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dersKodu =
        strtoupper(trim($_POST["dersKodu"] ?? ""));

    $dersAdi =
        trim($_POST["dersAdi"] ?? "");

    $akademisyen =
        trim($_POST["akademisyen"] ?? "");


    if ($dersKodu === "" || $dersAdi === "") {

        $hata =
            "Ders kodu ve ders adı zorunludur.";

    } else {

        // Ders kodu var mı?
        $kontrol = $baglanti->prepare("
            SELECT COUNT(*)
            FROM ders
            WHERE dersKodu = :dersKodu
        ");

        $kontrol->execute([
            "dersKodu" => $dersKodu
        ]);


        if ($kontrol->fetchColumn() > 0) {

            $hata =
                "Bu ders kodu zaten kullanılıyor.";

        } else {

            // Akademisyen seçilmişse kontrol et
            if ($akademisyen !== "") {

                $kontrolAkademisyen =
                    $baglanti->prepare("
                        SELECT COUNT(*)
                        FROM admin

                        WHERE kullaniciAdi = :akademisyen
                        AND unvan = 'akademisyen'
                    ");

                $kontrolAkademisyen->execute([
                    "akademisyen" => $akademisyen
                ]);


                if (
                    $kontrolAkademisyen->fetchColumn() == 0
                ) {

                    $hata =
                        "Seçilen akademisyen bulunamadı.";
                }
            }


            if ($hata === "") {

                $stmt = $baglanti->prepare("
                    INSERT INTO ders
                    (
                        dersKodu,
                        dersAdi,
                        akademisyen
                    )

                    VALUES
                    (
                        :dersKodu,
                        :dersAdi,
                        :akademisyen
                    )
                ");

                $stmt->execute([
                    "dersKodu" => $dersKodu,
                    "dersAdi" => $dersAdi,
                    "akademisyen" =>
                        $akademisyen !== ""
                        ? $akademisyen
                        : null
                ]);


                header(
                    "Location: /student_project/admin/courses/index.php?durum=eklendi"
                );

                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ders Ekle</title>

<style>

* {
    box-sizing: border-box;
}

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
    color: #0f172a;

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

input:focus,
select:focus {
    outline: none;

    border-color: #2563eb;
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

<h1>📚 Yeni Ders</h1>

<p class="description">
Yeni ders oluşturabilir ve akademisyen atayabilirsiniz.
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
    name="dersKodu"
    placeholder="Örn: BIL301"
    value="<?php echo htmlspecialchars($dersKodu); ?>"
    required
>

</div>


<div class="form-group">

<label>Ders Adı</label>

<input
    type="text"
    name="dersAdi"
    placeholder="Örn: Veritabanı Yönetim Sistemleri"
    value="<?php echo htmlspecialchars($dersAdi); ?>"
    required
>

</div>


<div class="form-group">

<label>Akademisyen</label>

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
    echo $akademisyen === $a["kullaniciAdi"]
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
    Dersi Kaydet
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