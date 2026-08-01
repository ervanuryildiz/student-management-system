<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$eskiKullaniciAdi = trim($_GET["kullaniciAdi"] ?? "");

if ($eskiKullaniciAdi === "") {
    header("Location: index.php?durum=bulunamadi");
    exit;
}


// Akademisyeni getir
$stmt = $baglanti->prepare("
    SELECT *
    FROM admin
    WHERE kullaniciAdi = :kullaniciAdi
      AND unvan = 'akademisyen'
");

$stmt->execute([
    "kullaniciAdi" => $eskiKullaniciAdi
]);

$akademisyen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$akademisyen) {
    header("Location: index.php?durum=bulunamadi");
    exit;
}

$hata = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $yeniKullaniciAdi = trim($_POST["kullaniciAdi"] ?? "");
    $ad = trim($_POST["ad"] ?? "");
    $soyad = trim($_POST["soyad"] ?? "");
    $sifre = $_POST["sifre"] ?? "";

    if ($yeniKullaniciAdi === "" || $ad === "" || $soyad === "") {

        $hata = "Ad, soyad ve kullanıcı adı boş bırakılamaz.";

    } else {

        // Başka kullanıcı aynı kullanıcı adını kullanıyor mu?
        $kontrol = $baglanti->prepare("
            SELECT COUNT(*)
            FROM admin
            WHERE kullaniciAdi = :yeni
              AND kullaniciAdi <> :eski
        ");

        $kontrol->execute([
            "yeni" => $yeniKullaniciAdi,
            "eski" => $eskiKullaniciAdi
        ]);

        if ($kontrol->fetchColumn() > 0) {

            $hata = "Bu kullanıcı adı başka bir hesap tarafından kullanılıyor.";

        } else {

            try {

                $baglanti->beginTransaction();


                // Şifre boş bırakıldıysa değiştirme
                if ($sifre !== "") {

                    $sql = "
                        UPDATE admin
                        SET
                            kullaniciAdi = :yeniKullaniciAdi,
                            ad = :ad,
                            soyad = :soyad,
                            sifre = :sifre

                        WHERE kullaniciAdi = :eskiKullaniciAdi
                          AND unvan = 'akademisyen'
                    ";

                    $stmt = $baglanti->prepare($sql);

                    $stmt->execute([
                        "yeniKullaniciAdi" => $yeniKullaniciAdi,
                        "ad" => $ad,
                        "soyad" => $soyad,
                        "sifre" => $sifre,
                        "eskiKullaniciAdi" => $eskiKullaniciAdi
                    ]);

                } else {

                    $sql = "
                        UPDATE admin
                        SET
                            kullaniciAdi = :yeniKullaniciAdi,
                            ad = :ad,
                            soyad = :soyad

                        WHERE kullaniciAdi = :eskiKullaniciAdi
                          AND unvan = 'akademisyen'
                    ";

                    $stmt = $baglanti->prepare($sql);

                    $stmt->execute([
                        "yeniKullaniciAdi" => $yeniKullaniciAdi,
                        "ad" => $ad,
                        "soyad" => $soyad,
                        "eskiKullaniciAdi" => $eskiKullaniciAdi
                    ]);
                }


                // Kullanıcı adı değişmişse derslerde de değiştir
                if ($yeniKullaniciAdi !== $eskiKullaniciAdi) {

                    $stmtDers = $baglanti->prepare("
                        UPDATE ders
                        SET akademisyen = :yeni
                        WHERE akademisyen = :eski
                    ");

                    $stmtDers->execute([
                        "yeni" => $yeniKullaniciAdi,
                        "eski" => $eskiKullaniciAdi
                    ]);
                }


                $baglanti->commit();

                header("Location: index.php?durum=guncellendi");
                exit;


            } catch (PDOException $e) {

                if ($baglanti->inTransaction()) {
                    $baglanti->rollBack();
                }

                $hata = "Akademisyen güncellenirken bir hata oluştu.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Akademisyen Düzenle</title>

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
    height: 72px;
    background: #0f172a;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.brand h2 {
    margin: 0;
    font-size: 18px;
}

.brand span {
    font-size: 12px;
    color: #94a3b8;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user {
    text-align: right;
}

.user strong {
    display: block;
    font-size: 14px;
}

.user span {
    color: #94a3b8;
    font-size: 12px;
}

.logout {
    padding: 9px 14px;
    background: #dc2626;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
}

.container {
    max-width: 850px;
    margin: 35px auto;
    padding: 0 20px;
}

.page-title {
    margin-bottom: 25px;
}

.page-title h1 {
    margin: 0 0 7px;
    font-size: 27px;
}

.page-title p {
    margin: 0;
    color: #64748b;
}

.card {
    background: white;
    border: 1px solid #e5eaf1;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,.03);
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e5eaf1;
}

.card-header h2 {
    margin: 0 0 5px;
    font-size: 18px;
}

.card-header p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.card-body {
    padding: 25px;
}

.profile {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 25px;
}

.avatar {
    width: 48px;
    height: 48px;
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.profile strong {
    display: block;
}

.profile span {
    color: #64748b;
    font-size: 12px;
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
    font-size: 14px;
}

input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
}

.help {
    margin-top: 5px;
    color: #94a3b8;
    font-size: 12px;
}

.error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 12px 15px;
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
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.cancel {
    background: #f1f5f9;
    color: #475569;
}

.save {
    background: #0284c7;
    color: white;
}

.save:hover {
    background: #0369a1;
}

@media(max-width:650px) {

    .header {
        padding: 0 18px;
    }

    .user {
        display: none;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}

</style>

</head>

<body>


<header class="header">

    <div class="brand">

        <div class="brand-icon">🎓</div>

        <div>
            <h2>Öğrenci Takip Sistemi</h2>
            <span>Yönetim Paneli</span>
        </div>

    </div>


    <div class="header-right">

        <div class="user">

            <strong>
                <?php echo htmlspecialchars($_SESSION["ad_soyad"] ?? "Admin"); ?>
            </strong>

            <span>Sistem Yöneticisi</span>

        </div>

        <a href="/student_project/logout.php" class="logout">
            Çıkış Yap
        </a>

    </div>

</header>


<main class="container">


    <div class="page-title">

        <h1>Akademisyen Düzenle</h1>

        <p>
            Akademisyenin hesap ve kişisel bilgilerini güncelleyebilirsiniz.
        </p>

    </div>


    <?php if ($hata !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($hata); ?>
        </div>

    <?php endif; ?>


    <div class="card">


        <div class="card-header">

            <h2>Hesap Bilgileri</h2>

            <p>
                Değiştirmek istediğiniz alanları düzenleyin.
            </p>

        </div>


        <div class="card-body">


            <div class="profile">

                <div class="avatar">

                    <?php
                    echo htmlspecialchars(
                        mb_strtoupper(mb_substr($akademisyen["ad"], 0, 1))
                        .
                        mb_strtoupper(mb_substr($akademisyen["soyad"], 0, 1))
                    );
                    ?>

                </div>

                <div>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $akademisyen["ad"] . " " . $akademisyen["soyad"]
                        );
                        ?>

                    </strong>

                    <span>
                        @<?php echo htmlspecialchars($akademisyen["kullaniciAdi"]); ?>
                    </span>

                </div>

            </div>


            <form method="POST">


                <div class="form-row">


                    <div class="form-group">

                        <label>Ad</label>

                        <input
                            type="text"
                            name="ad"
                            value="<?php echo htmlspecialchars($_POST["ad"] ?? $akademisyen["ad"]); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Soyad</label>

                        <input
                            type="text"
                            name="soyad"
                            value="<?php echo htmlspecialchars($_POST["soyad"] ?? $akademisyen["soyad"]); ?>"
                            required
                        >

                    </div>


                </div>


                <div class="form-group">

                    <label>Kullanıcı Adı</label>

                    <input
                        type="text"
                        name="kullaniciAdi"
                        value="<?php echo htmlspecialchars($_POST["kullaniciAdi"] ?? $akademisyen["kullaniciAdi"]); ?>"
                        required
                    >

                    <div class="help">
                        Kullanıcı adı değiştirilirse akademisyene atanmış dersler de yeni kullanıcı adına aktarılır.
                    </div>

                </div>


                <div class="form-group">

                    <label>Yeni Şifre</label>

                    <input
                        type="password"
                        name="sifre"
                        placeholder="Değiştirmek istemiyorsanız boş bırakın"
                    >

                    <div class="help">
                        Boş bırakırsanız mevcut şifre korunur.
                    </div>

                </div>


                <div class="actions">

                    <a href="index.php" class="btn cancel">
                        İptal
                    </a>

                    <button type="submit" class="btn save">
                        Değişiklikleri Kaydet
                    </button>

                </div>


            </form>

        </div>

    </div>

</main>

</body>
</html>