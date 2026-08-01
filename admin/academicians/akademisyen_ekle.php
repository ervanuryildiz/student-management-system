<?php

session_start();


// ==========================================
// SADECE ADMIN ERİŞEBİLİR
// ==========================================

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}


require_once __DIR__ . '/../../database.php';


$hata = "";

$kullaniciAdi = "";
$ad = "";
$soyad = "";


// ==========================================
// FORM GÖNDERİLDİ
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kullaniciAdi = trim($_POST["kullaniciAdi"] ?? "");
    $ad            = trim($_POST["ad"] ?? "");
    $soyad         = trim($_POST["soyad"] ?? "");

    // Şifrede trim kullanmıyoruz
    $sifre = $_POST["sifre"] ?? "";


    // ======================================
    // BOŞ ALAN KONTROLÜ
    // ======================================

    if (
        $kullaniciAdi === "" ||
        $ad === "" ||
        $soyad === "" ||
        $sifre === ""
    ) {

        $hata = "Lütfen tüm alanları doldurun.";

    }

    // ======================================
    // ŞİFRE UZUNLUĞU
    // ======================================

    elseif (strlen($sifre) < 6) {

        $hata = "Şifre en az 6 karakter olmalıdır.";

    }

    else {

        try {

            // ==================================
            // KULLANICI ADI KONTROLÜ
            // ==================================

            $kontrol = $baglanti->prepare("
                SELECT kullaniciAdi
                FROM admin
                WHERE kullaniciAdi = :kullaniciAdi
                LIMIT 1
            ");

            $kontrol->execute([
                "kullaniciAdi" => $kullaniciAdi
            ]);


            if ($kontrol->fetch(PDO::FETCH_ASSOC)) {

                $hata =
                    "Bu kullanıcı adı zaten kullanılıyor.";

            }

            else {

                // ==================================
                // ŞİFREYİ HASHLE
                // ==================================

                $sifreHash = password_hash(
                    $sifre,
                    PASSWORD_DEFAULT
                );


                if ($sifreHash === false) {

                    $hata =
                        "Şifre oluşturulurken bir hata meydana geldi.";

                }

                else {

                    // ==================================
                    // AKADEMİSYEN EKLE
                    // ==================================

                    $sql = "
                        INSERT INTO admin
                        (
                            kullaniciAdi,
                            sifre,
                            ad,
                            soyad,
                            unvan
                        )

                        VALUES
                        (
                            :kullaniciAdi,
                            :sifre,
                            :ad,
                            :soyad,
                            'akademisyen'
                        )
                    ";


                    $stmt = $baglanti->prepare($sql);


                    $stmt->execute([

                        "kullaniciAdi" =>
                            $kullaniciAdi,

                        "sifre" =>
                            $sifreHash,

                        "ad" =>
                            $ad,

                        "soyad" =>
                            $soyad

                    ]);


                    header(
                        "Location: index.php?durum=eklendi"
                    );

                    exit;
                }
            }

        }

        catch (PDOException $e) {

            $hata =
                "Akademisyen eklenirken bir veritabanı hatası oluştu.";
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

<title>Yeni Akademisyen</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        'Segoe UI',
        Arial,
        sans-serif;

    background: #f5f7fb;

    color: #172033;
}


/* ==========================================
   HEADER
========================================== */

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

    background: #2563eb;

    border-radius: 10px;

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

    color: #94a3b8;

    font-size: 12px;
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

    font-size: 12px;

    color: #94a3b8;
}


.logout {

    background: #dc2626;

    color: white;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;
}


/* ==========================================
   CONTAINER
========================================== */

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

    color: #0f172a;

    font-size: 27px;
}


.page-title p {

    margin: 0;

    color: #64748b;
}


/* ==========================================
   CARD
========================================== */

.card {

    background: white;

    border: 1px solid #e5eaf1;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 2px 5px
        rgba(0,0,0,.03);
}


.card-header {

    padding: 20px 25px;

    border-bottom:
        1px solid #e5eaf1;
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


/* ==========================================
   FORM
========================================== */

.form-row {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;
}


.form-group {

    margin-bottom: 20px;
}


label {

    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 600;

    color: #334155;
}


input {

    width: 100%;

    padding: 11px 13px;

    border:
        1px solid #cbd5e1;

    border-radius: 7px;

    font-size: 14px;

    outline: none;
}


input:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.10);
}


.help {

    margin-top: 6px;

    color: #94a3b8;

    font-size: 11px;

    line-height: 1.5;
}


/* ==========================================
   PASSWORD
========================================== */

.password-wrapper {

    position: relative;
}


.password-wrapper input {

    padding-right: 45px;
}


.password-toggle {

    position: absolute;

    right: 12px;

    top: 50%;

    transform:
        translateY(-50%);

    border: none;

    background: transparent;

    cursor: pointer;

    font-size: 16px;
}


/* ==========================================
   ROLE
========================================== */

.role-box {

    background: #eff6ff;

    border:
        1px solid #dbeafe;

    color: #1d4ed8;

    padding: 12px 14px;

    border-radius: 7px;

    font-size: 13px;

    margin-bottom: 20px;
}


/* ==========================================
   ERROR
========================================== */

.error {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fecaca;

    padding: 12px 15px;

    border-radius: 7px;

    margin-bottom: 20px;
}


/* ==========================================
   BUTTONS
========================================== */

.actions {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    padding-top: 5px;
}


.btn {

    padding: 10px 17px;

    border-radius: 7px;

    border: none;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;

    font-weight: 600;
}


.btn-cancel {

    background: #f1f5f9;

    color: #475569;
}


.btn-cancel:hover {

    background: #e2e8f0;
}


.btn-save {

    background: #16a34a;

    color: white;
}


.btn-save:hover {

    background: #15803d;
}


/* ==========================================
   RESPONSIVE
========================================== */

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


        <div class="brand-icon">
            🎓
        </div>


        <div>

            <h2>
                Öğrenci Takip Sistemi
            </h2>

            <span>
                Yönetim Paneli
            </span>

        </div>


    </div>


    <div class="header-right">


        <div class="user">


            <strong>

                <?php

                echo htmlspecialchars(
                    $_SESSION["ad_soyad"]
                    ?? "Admin"
                );

                ?>

            </strong>


            <span>
                Sistem Yöneticisi
            </span>


        </div>


        <a
            href="/student_project/logout.php"
            class="logout"
        >
            Çıkış Yap
        </a>


    </div>


</header>


<main class="container">


    <div class="page-title">


        <h1>
            Yeni Akademisyen Ekle
        </h1>


        <p>
            Sisteme yeni bir akademisyen hesabı
            oluşturabilirsiniz.
        </p>


    </div>


    <?php if ($hata !== ""): ?>


        <div class="error">

            ⚠️

            <?php
            echo htmlspecialchars($hata);
            ?>

        </div>


    <?php endif; ?>


    <div class="card">


        <div class="card-header">


            <h2>
                Akademisyen Bilgileri
            </h2>


            <p>
                Akademisyenin kişisel ve giriş
                bilgilerini girin.
            </p>


        </div>


        <div class="card-body">


            <form method="POST">


                <!-- AD / SOYAD -->

                <div class="form-row">


                    <div class="form-group">


                        <label>
                            Ad
                        </label>


                        <input
                            type="text"
                            name="ad"
                            value="<?php
                            echo htmlspecialchars($ad);
                            ?>"
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
                            value="<?php
                            echo htmlspecialchars($soyad);
                            ?>"
                            required
                        >


                    </div>


                </div>


                <!-- KULLANICI ADI -->

                <div class="form-group">


                    <label>
                        Kullanıcı Adı
                    </label>


                    <input
                        type="text"
                        name="kullaniciAdi"
                        value="<?php
                        echo htmlspecialchars(
                            $kullaniciAdi
                        );
                        ?>"
                        autocomplete="off"
                        required
                    >


                    <div class="help">
                        Akademisyen sisteme bu kullanıcı
                        adıyla giriş yapacaktır.
                    </div>


                </div>


                <!-- ŞİFRE -->

                <div class="form-group">


                    <label>
                        Şifre
                    </label>


                    <div class="password-wrapper">


                        <input
                            type="password"
                            name="sifre"
                            id="sifre"
                            minlength="6"
                            autocomplete="new-password"
                            placeholder="En az 6 karakter"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="sifreGoster()"
                            title="Şifreyi göster/gizle"
                        >
                            👁
                        </button>


                    </div>


                    <div class="help">
                        En az 6 karakterden oluşan geçici
                        bir şifre belirleyiniz. Şifre
                        veritabanında hashlenerek
                        saklanacaktır.
                    </div>


                </div>


                <!-- ROL -->

                <div class="role-box">

                    👨‍🏫

                    Bu kullanıcı sisteme

                    <strong>
                        Akademisyen
                    </strong>

                    yetkisiyle eklenecektir.

                </div>


                <!-- BUTTONS -->

                <div class="actions">


                    <a
                        href="index.php"
                        class="btn btn-cancel"
                    >
                        İptal
                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Akademisyeni Kaydet
                    </button>


                </div>


            </form>


        </div>


    </div>


</main>


<script>

function sifreGoster() {

    const sifre =
        document.getElementById("sifre");


    if (sifre.type === "password") {

        sifre.type = "text";

    }

    else {

        sifre.type = "password";

    }
}

</script>


</body>

</html>