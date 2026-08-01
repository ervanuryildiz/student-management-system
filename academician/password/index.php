<?php

require_once __DIR__ . '/../../includes/auth.php';
rolKontrol("akademisyen");

require_once __DIR__ . '/../../database.php';

$kullaniciAdi = $_SESSION["kullanici"] ?? "";

$hata = "";
$basarili = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mevcut = $_POST["mevcutSifre"] ?? "";
    $yeni = $_POST["yeniSifre"] ?? "";
    $tekrar = $_POST["yeniSifreTekrar"] ?? "";


    if ($mevcut === "" || $yeni === "" || $tekrar === "") {

        $hata = "Lütfen tüm alanları doldurun.";

    } elseif (strlen($yeni) < 6) {

        $hata = "Yeni şifre en az 6 karakter olmalıdır.";

    } elseif ($yeni !== $tekrar) {

        $hata = "Yeni şifreler eşleşmiyor.";

    } elseif ($mevcut === $yeni) {

        $hata = "Yeni şifre mevcut şifreden farklı olmalıdır.";

    } else {

        $stmt = $baglanti->prepare("
            SELECT sifre
            FROM admin
            WHERE kullaniciAdi = :kullaniciAdi
            AND unvan = 'akademisyen'
            LIMIT 1
        ");

        $stmt->execute([
            "kullaniciAdi" => $kullaniciAdi
        ]);

        $kayit = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$kayit) {

            $hata = "Akademisyen hesabı bulunamadı.";

        } else {

            $dogru =
                password_verify(
                    $mevcut,
                    $kayit["sifre"]
                );


            // Eski düz metin desteği
            if (!$dogru) {

                $dogru = hash_equals(
                    (string)$kayit["sifre"],
                    (string)$mevcut
                );
            }


            if (!$dogru) {

                $hata = "Mevcut şifreniz yanlış.";

            } else {

                $hash = password_hash(
                    $yeni,
                    PASSWORD_DEFAULT
                );


                $stmt = $baglanti->prepare("
                    UPDATE admin
                    SET sifre = :sifre
                    WHERE kullaniciAdi = :kullaniciAdi
                    AND unvan = 'akademisyen'
                ");

                $stmt->execute([
                    "sifre" => $hash,
                    "kullaniciAdi" => $kullaniciAdi
                ]);


                $basarili =
                    "Şifreniz başarıyla değiştirildi.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Şifre Değiştir</title>

<link
    rel="stylesheet"
    href="/student_project/assets/css/style.css"
>

<style>

.password-card {
    max-width: 600px;
    padding: 27px;
}

.form-group {
    margin-bottom: 19px;
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

.btn {
    width: 100%;

    padding: 11px;

    border: none;
    border-radius: 7px;

    background: #2563eb;
    color: white;

    cursor: pointer;

    font-weight: 600;
}

.alert {
    padding: 12px 14px;
    margin-bottom: 20px;

    border-radius: 7px;

    font-size: 13px;
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

<h1>Şifre Değiştir</h1>

<p>
    Hesabınızın giriş şifresini güvenli bir şekilde değiştirebilirsiniz.
</p>

</div>


<div class="card password-card">

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

<div class="form-group">

<label>Mevcut Şifre</label>

<input
    type="password"
    name="mevcutSifre"
    required
>

</div>


<div class="form-group">

<label>Yeni Şifre</label>

<input
    type="password"
    name="yeniSifre"
    minlength="6"
    required
>

</div>


<div class="form-group">

<label>Yeni Şifre Tekrar</label>

<input
    type="password"
    name="yeniSifreTekrar"
    minlength="6"
    required
>

</div>


<button
    type="submit"
    class="btn"
>
    Şifreyi Değiştir
</button>

</form>

</div>

</main>
</div>

</body>
</html>