<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$hata = "";


// =====================================================
// ÖĞRENCİ NO KONTROLÜ
// =====================================================

if (!isset($_GET["ogrenciNo"]) || trim($_GET["ogrenciNo"]) === "") {
    header("Location: index.php");
    exit;
}

$eskiOgrenciNo = trim($_GET["ogrenciNo"]);


// =====================================================
// ÖĞRENCİYİ GETİR
// =====================================================

$stmt = $baglanti->prepare("
    SELECT *
    FROM ogrenci
    WHERE ogrenciNo = :ogrenciNo
");

$stmt->execute([
    "ogrenciNo" => $eskiOgrenciNo
]);

$ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$ogrenci) {

    header("Location: index.php");
    exit;

}


// =====================================================
// FORM GÖNDERİLDİ
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ad = trim($_POST["ad"] ?? "");
    $soyad = trim($_POST["soyad"] ?? "");
    $bolum = trim($_POST["bolum"] ?? "");
    $sifre = trim($_POST["sifre"] ?? "");


    if (
        $ad === "" ||
        $soyad === "" ||
        $bolum === ""
    ) {

        $hata = "Ad, soyad ve bölüm alanları boş bırakılamaz.";

    } else {

        try {

            // Şifre değiştirilmediyse mevcut şifre korunur

            if ($sifre !== "") {

                $sql = "
                    UPDATE ogrenci

                    SET
                        ad = :ad,
                        soyad = :soyad,
                        bolum = :bolum,
                        sifre = :sifre

                    WHERE ogrenciNo = :ogrenciNo
                ";

                $stmt = $baglanti->prepare($sql);

                $stmt->execute([
                    "ad" => $ad,
                    "soyad" => $soyad,
                    "bolum" => $bolum,
                    "sifre" => $sifre,
                    "ogrenciNo" => $eskiOgrenciNo
                ]);

            } else {

                $sql = "
                    UPDATE ogrenci

                    SET
                        ad = :ad,
                        soyad = :soyad,
                        bolum = :bolum

                    WHERE ogrenciNo = :ogrenciNo
                ";

                $stmt = $baglanti->prepare($sql);

                $stmt->execute([
                    "ad" => $ad,
                    "soyad" => $soyad,
                    "bolum" => $bolum,
                    "ogrenciNo" => $eskiOgrenciNo
                ]);

            }


            header(
                "Location: index.php?durum=guncellendi"
            );

            exit;

        } catch (PDOException $e) {

            $hata = "Öğrenci güncellenirken hata oluştu.";

        }

    }

}


// =====================================================
// BÖLÜMLER
// =====================================================

$bolumStmt = $baglanti->query("
    SELECT DISTINCT bolum
    FROM ogrenci
    WHERE bolum IS NOT NULL
    AND bolum != ''
    ORDER BY bolum
");

$bolumler = $bolumStmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Öğrenci Düzenle</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: 'Segoe UI', Arial, sans-serif;

    background: #f5f7fb;

    color: #172033;

}

.header {

    background: #0f172a;

    color: white;

    padding: 20px 40px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.header a {

    color: white;

    text-decoration: none;

    background: #334155;

    padding: 10px 15px;

    border-radius: 7px;

}

.container {

    max-width: 800px;

    margin: 40px auto;

    padding: 0 20px;

}

.card {

    background: white;

    border: 1px solid #e5eaf1;

    border-radius: 12px;

    padding: 30px;

    box-shadow:
        0 3px 10px rgba(0,0,0,.04);

}

h1 {

    margin-top: 0;

    margin-bottom: 5px;

}

.description {

    color: #64748b;

    margin-bottom: 30px;

}

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;

}

.form-group {

    margin-bottom: 20px;

}

label {

    display: block;

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 8px;

}

input,
select {

    width: 100%;

    height: 45px;

    border: 1px solid #dbe1e8;

    border-radius: 8px;

    padding: 0 13px;

    font-family: inherit;

    outline: none;

}

input:focus,
select:focus {

    border-color: #2563eb;

}

.readonly {

    background: #f1f5f9;

    color: #64748b;

}

.help {

    color: #94a3b8;

    font-size: 11px;

    margin-top: 6px;

}

.actions {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    border-top: 1px solid #e5eaf1;

    padding-top: 20px;

}

.btn {

    padding: 11px 17px;

    border-radius: 8px;

    text-decoration: none;

    border: none;

    cursor: pointer;

    font-weight: 600;

}

.btn-save {

    background: #2563eb;

    color: white;

}

.btn-back {

    background: #e2e8f0;

    color: #475569;

}

.error {

    background: #fee2e2;

    color: #991b1b;

    padding: 13px;

    border-radius: 7px;

    margin-bottom: 20px;

}

@media(max-width:650px) {

    .form-row {

        grid-template-columns: 1fr;

    }

}

</style>

</head>

<body>


<div class="header">

    <strong>
        🎓 Öğrenci Takip Sistemi
    </strong>

    <a href="index.php">
        ← Öğrencilere Dön
    </a>

</div>


<div class="container">

<div class="card">

<h1>
    Öğrenci Düzenle
</h1>

<p class="description">
    Öğrencinin kayıt bilgilerini güncelleyebilirsiniz.
</p>


<?php if ($hata): ?>

<div class="error">

    <?php echo htmlspecialchars($hata); ?>

</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
    Öğrenci Numarası
</label>

<input
    type="text"
    value="<?php echo htmlspecialchars($ogrenci["ogrenciNo"]); ?>"
    class="readonly"
    readonly
>

<div class="help">
    Öğrenci numarası sistem ilişkileri nedeniyle bu ekrandan değiştirilemez.
</div>

</div>


<div class="form-row">


<div class="form-group">

<label>
    Ad
</label>

<input
    type="text"
    name="ad"
    value="<?php echo htmlspecialchars($ogrenci["ad"]); ?>"
    required
>

</div>


<div class="form-group">

<label>
    Soyad
</label>

<input
    type="text"
    name="soyad"
    value="<?php echo htmlspecialchars($ogrenci["soyad"]); ?>"
    required
>

</div>


</div>


<div class="form-group">

<label>
    Bölüm
</label>

<input
    type="text"
    name="bolum"
    list="bolumler"
    value="<?php echo htmlspecialchars($ogrenci["bolum"]); ?>"
    required
>

<datalist id="bolumler">

<?php foreach ($bolumler as $bolum): ?>

<option
    value="<?php echo htmlspecialchars($bolum); ?>"
>

<?php endforeach; ?>

</datalist>

</div>


<div class="form-group">

<label>
    Yeni Şifre
</label>

<input
    type="password"
    name="sifre"
    placeholder="Değiştirmek istemiyorsanız boş bırakın"
>

<div class="help">
    Boş bırakırsanız öğrencinin mevcut şifresi korunur.
</div>

</div>


<div class="actions">

<a
    href="index.php"
    class="btn btn-back"
>
    İptal
</a>

<button
    type="submit"
    class="btn btn-save"
>
    ✓ Değişiklikleri Kaydet
</button>

</div>


</form>

</div>

</div>

</body>

</html>