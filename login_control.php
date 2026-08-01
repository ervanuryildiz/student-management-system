<?php

session_start();

require_once __DIR__ . '/database.php';


// ==========================================
// SADECE POST İSTEĞİ
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /student_project/login.php");
    exit;
}


// ==========================================
// FORM VERİLERİ
// ==========================================

$giris_tipi = $_POST["giris_tipi"] ?? "ogrenci";
$kullanici  = trim($_POST["kullanici"] ?? "");
$sifre      = $_POST["sifre"] ?? "";


if ($kullanici === "" || $sifre === "") {
    header("Location: /student_project/login.php?hata=hatali_giris");
    exit;
}


// ==========================================
// ŞİFRE KONTROL FONKSİYONU
// ==========================================

function sifreKontrolEt($girilenSifre, $veritabaniSifresi)
{
    $veritabaniSifresi = (string)$veritabaniSifresi;

    // Hashlenmiş şifre
    if (password_verify($girilenSifre, $veritabaniSifresi)) {
        return true;
    }

    // Eski düz metin şifre desteği
    if (hash_equals($veritabaniSifresi, (string)$girilenSifre)) {
        return true;
    }

    return false;
}


// ==========================================
// ESKİ ŞİFREYİ HASH'E ÇEVİRME
// ==========================================

function hashlenmisMi($sifre)
{
    $bilgi = password_get_info((string)$sifre);

    return !empty($bilgi["algo"]);
}


// ==========================================
// ÖĞRENCİ GİRİŞİ
// ==========================================

if ($giris_tipi === "ogrenci") {

    $stmt = $baglanti->prepare("
        SELECT
            ogrenciNo,
            ad,
            soyad,
            bolum,
            sifre
        FROM ogrenci
        WHERE ogrenciNo = :ogrenciNo
        LIMIT 1
    ");

    $stmt->execute([
        "ogrenciNo" => $kullanici
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if (
        $user &&
        sifreKontrolEt(
            $sifre,
            $user["sifre"]
        )
    ) {

        // ----------------------------------
        // Düz metinse otomatik hashle
        // ----------------------------------

        if (!hashlenmisMi($user["sifre"])) {

            $hash = password_hash(
                $sifre,
                PASSWORD_DEFAULT
            );

            $guncelle = $baglanti->prepare("
                UPDATE ogrenci
                SET sifre = :sifre
                WHERE ogrenciNo = :ogrenciNo
            ");

            $guncelle->execute([
                "sifre" => $hash,
                "ogrenciNo" => $user["ogrenciNo"]
            ]);
        }


        // ----------------------------------
        // SESSION
        // ----------------------------------

        session_regenerate_id(true);

        $_SESSION["kullanici"] = $user["ogrenciNo"];

        $_SESSION["ad_soyad"] =
            $user["ad"] . " " . $user["soyad"];

        $_SESSION["bolum"] = $user["bolum"];

        $_SESSION["rol"] = "ogrenci";


        // ----------------------------------
        // ÖĞRENCİ PANELİ
        // ----------------------------------

        header(
            "Location: /student_project/student/dashboard.php"
        );

        exit;
    }
}


// ==========================================
// ADMIN / AKADEMİSYEN GİRİŞİ
// ==========================================

elseif ($giris_tipi === "akademisyen") {

    $stmt = $baglanti->prepare("
        SELECT
            kullaniciAdi,
            sifre,
            ad,
            soyad,
            unvan
        FROM admin
        WHERE kullaniciAdi = :kullaniciAdi
        LIMIT 1
    ");

    $stmt->execute([
        "kullaniciAdi" => $kullanici
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if (
        $user &&
        sifreKontrolEt(
            $sifre,
            $user["sifre"]
        )
    ) {

        // ----------------------------------
        // Rol kontrolü
        // ----------------------------------

        if (
            $user["unvan"] !== "admin" &&
            $user["unvan"] !== "akademisyen"
        ) {

            header(
                "Location: /student_project/login.php?hata=hatali_giris"
            );

            exit;
        }


        // ----------------------------------
        // Düz metinse otomatik hashle
        // ----------------------------------

        if (!hashlenmisMi($user["sifre"])) {

            $hash = password_hash(
                $sifre,
                PASSWORD_DEFAULT
            );

            $guncelle = $baglanti->prepare("
                UPDATE admin
                SET sifre = :sifre
                WHERE kullaniciAdi = :kullaniciAdi
            ");

            $guncelle->execute([
                "sifre" => $hash,
                "kullaniciAdi" => $user["kullaniciAdi"]
            ]);
        }


        // ----------------------------------
        // SESSION
        // ----------------------------------

        session_regenerate_id(true);

        $_SESSION["kullanici"] =
            $user["kullaniciAdi"];

        $_SESSION["ad_soyad"] =
            $user["ad"] . " " . $user["soyad"];

        $_SESSION["unvan"] =
            $user["unvan"];

        $_SESSION["rol"] =
            $user["unvan"];


        // ==================================
        // ADMIN PANELİ
        // ==================================

        if ($user["unvan"] === "admin") {

            header(
                "Location: /student_project/admin/dashboard.php"
            );

            exit;
        }


        // ==================================
        // AKADEMİSYEN PANELİ
        // ==================================

        if ($user["unvan"] === "akademisyen") {

            header(
                "Location: /student_project/academician/dashboard.php"
            );

            exit;
        }
    }
}


// ==========================================
// GİRİŞ BAŞARISIZ
// ==========================================

header(
    "Location: /student_project/login.php?hata=hatali_giris"
);

exit;

?>