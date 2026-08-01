<?php

session_start();

// Sadece admin erişebilir
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}

require_once '../../database.php';


// ==========================================
// ÖĞRENCİ NUMARASINI AL
// ==========================================

$ogrenciNo = trim($_GET["ogrenciNo"] ?? "");

if ($ogrenciNo === "") {
    header("Location: index.php");
    exit;
}


// ==========================================
// ÖĞRENCİYİ GETİR
// ==========================================

$sql = "
    SELECT
        ogrenciNo,
        ad,
        soyad,
        bolum
    FROM ogrenci
    WHERE ogrenciNo = :ogrenciNo
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);


// Öğrenci bulunamadıysa listeye dön
if (!$ogrenci) {
    header("Location: index.php");
    exit;
}


// ==========================================
// DERS EKLE / DERSTEN ÇIKAR
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $islem = $_POST["islem"] ?? "";
    $dersKodu = trim($_POST["dersKodu"] ?? "");


    // ======================================
    // DERS ATA
    // ======================================

    if ($islem === "ekle" && $dersKodu !== "") {


        // ----------------------------------
        // Ders var mı ve öğrencinin
        // bölümüyle aynı mı?
        // ----------------------------------

        $sql = "
            SELECT
                dersKodu,
                dersAdi,
                bolum
            FROM ders
            WHERE dersKodu = :dersKodu
            AND bolum = :bolum
        ";

        $stmt = $baglanti->prepare($sql);

        $stmt->execute([
            "dersKodu" => $dersKodu,
            "bolum" => $ogrenci["bolum"]
        ]);

        $ders = $stmt->fetch(PDO::FETCH_ASSOC);


        // Ders yoksa veya başka bölüme aitse
        if (!$ders) {

            header(
                "Location: dersler.php?ogrenciNo=" .
                urlencode($ogrenciNo) .
                "&durum=uygunsuz_ders"
            );

            exit;
        }


        // ----------------------------------
        // Öğrenci bu dersi zaten alıyor mu?
        // ----------------------------------

        $sql = "
            SELECT dersKodu
            FROM ogrenci_ders
            WHERE ogrenciNo = :ogrenciNo
            AND dersKodu = :dersKodu
        ";

        $stmt = $baglanti->prepare($sql);

        $stmt->execute([
            "ogrenciNo" => $ogrenciNo,
            "dersKodu" => $dersKodu
        ]);

        $kayit = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($kayit) {

            header(
                "Location: dersler.php?ogrenciNo=" .
                urlencode($ogrenciNo) .
                "&durum=zaten_var"
            );

            exit;
        }


        // ----------------------------------
        // Dersi öğrenciye ata
        // ----------------------------------

        $sql = "
            INSERT INTO ogrenci_ders
                (ogrenciNo, dersKodu)
            VALUES
                (:ogrenciNo, :dersKodu)
        ";

        $stmt = $baglanti->prepare($sql);

        $stmt->execute([
            "ogrenciNo" => $ogrenciNo,
            "dersKodu" => $dersKodu
        ]);


        header(
            "Location: dersler.php?ogrenciNo=" .
            urlencode($ogrenciNo) .
            "&durum=eklendi"
        );

        exit;
    }



    // ======================================
    // DERSTEN ÇIKAR
    // ======================================

    if ($islem === "sil" && $dersKodu !== "") {

        $sql = "
            DELETE FROM ogrenci_ders
            WHERE ogrenciNo = :ogrenciNo
            AND dersKodu = :dersKodu
        ";

        $stmt = $baglanti->prepare($sql);

        $stmt->execute([
            "ogrenciNo" => $ogrenciNo,
            "dersKodu" => $dersKodu
        ]);


        header(
            "Location: dersler.php?ogrenciNo=" .
            urlencode($ogrenciNo) .
            "&durum=silindi"
        );

        exit;
    }
}


// ==========================================
// ÖĞRENCİNİN ALDIĞI DERSLER
// ==========================================

$sql = "
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.bolum,
        d.akademisyen

    FROM ogrenci_ders od

    INNER JOIN ders d
        ON od.dersKodu = d.dersKodu

    WHERE od.ogrenciNo = :ogrenciNo

    ORDER BY d.dersKodu ASC
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "ogrenciNo" => $ogrenciNo
]);

$kayitliDersler = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// ATANABİLECEK DERSLER
//
// Sadece öğrencinin kendi bölümündeki
// ve henüz almadığı dersler gösterilir.
// ==========================================

$sql = "
    SELECT
        d.dersKodu,
        d.dersAdi,
        d.bolum,
        d.akademisyen

    FROM ders d

    WHERE d.bolum = :bolum

    AND NOT EXISTS (

        SELECT 1

        FROM ogrenci_ders od

        WHERE od.ogrenciNo = :ogrenciNo
        AND od.dersKodu = d.dersKodu
    )

    ORDER BY d.dersKodu ASC
";

$stmt = $baglanti->prepare($sql);

$stmt->execute([
    "bolum" => $ogrenci["bolum"],
    "ogrenciNo" => $ogrenciNo
]);

$atanabilirDersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Öğrenci Ders Yönetimi</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            font-family: Arial, sans-serif;

            background-color: #f4f6f8;

            margin: 0;

            padding: 30px;
        }


        .container {

            max-width: 1100px;

            margin: auto;

            background-color: white;

            padding: 25px;

            border-radius: 10px;
        }


        h1,
        h2 {

            color: #1e293b;
        }


        h1 {

            margin-top: 0;

            margin-bottom: 5px;
        }


        .ogrenci-bilgisi {

            color: #64748b;

            margin-bottom: 25px;
        }



        /* =========================
           BUTONLAR
        ========================= */


        .btn {

            display: inline-block;

            padding: 10px 15px;

            text-decoration: none;

            border-radius: 5px;

            color: white;

            border: none;

            cursor: pointer;

            font-size: 14px;
        }


        .btn-geri {

            background-color: #64748b;
        }


        .btn-geri:hover {

            background-color: #475569;
        }


        .btn-ekle {

            background-color: #16a34a;
        }


        .btn-ekle:hover {

            background-color: #15803d;
        }


        .btn-sil {

            background-color: #dc2626;
        }


        .btn-sil:hover {

            background-color: #b91c1c;
        }



        /* =========================
           ÜST MENÜ
        ========================= */


        .ust-menu {

            margin-bottom: 25px;
        }



        /* =========================
           BÖLÜMLER
        ========================= */


        .bolum {

            margin-top: 30px;

            padding-top: 25px;

            border-top: 1px solid #e2e8f0;
        }


        .bolum h2 {

            margin-top: 0;

            margin-bottom: 20px;
        }



        /* =========================
           DERS EKLEME FORMU
        ========================= */


        .ders-form {

            display: flex;

            gap: 10px;

            align-items: center;
        }


        select {

            flex: 1;

            padding: 11px;

            border: 1px solid #cbd5e1;

            border-radius: 5px;

            background-color: white;

            font-size: 14px;
        }



        /* =========================
           TABLO
        ========================= */


        table {

            width: 100%;

            border-collapse: collapse;
        }


        th,
        td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

            text-align: left;
        }


        th {

            background-color: #1e293b;

            color: white;
        }


        tbody tr:hover {

            background-color: #f1f5f9;
        }



        /* =========================
           MESAJLAR
        ========================= */


        .basarili {

            background-color: #dcfce7;

            color: #166534;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 20px;
        }


        .uyari {

            background-color: #fef3c7;

            color: #92400e;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 20px;
        }


        .hata {

            background-color: #fee2e2;

            color: #991b1b;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 20px;
        }


        .bos {

            background-color: #f8fafc;

            color: #64748b;

            padding: 20px;

            border-radius: 5px;

            border: 1px solid #e2e8f0;
        }



        /* =========================
           MOBİL
        ========================= */


        @media (max-width: 700px) {

            body {

                padding: 15px;
            }


            .ders-form {

                flex-direction: column;

                align-items: stretch;
            }


            .container {

                overflow-x: auto;
            }
        }

    </style>

</head>


<body>


<div class="container">


    <!-- =====================================
         GERİ DÖN
    ====================================== -->


    <div class="ust-menu">

        <a
            href="index.php"
            class="btn btn-geri"
        >
            ← Öğrenci İşlemlerine Dön
        </a>

    </div>



    <!-- =====================================
         ÖĞRENCİ BİLGİLERİ
    ====================================== -->


    <h1>
        📚 Ders Yönetimi
    </h1>


    <p class="ogrenci-bilgisi">


        Öğrenci:

        <strong>

            <?php

            echo htmlspecialchars(
                $ogrenci["ad"] . " " . $ogrenci["soyad"]
            );

            ?>

        </strong>


        &nbsp; | &nbsp;


        Öğrenci No:

        <strong>

            <?php
            echo htmlspecialchars(
                $ogrenci["ogrenciNo"]
            );
            ?>

        </strong>


        &nbsp; | &nbsp;


        Bölüm:

        <strong>

            <?php
            echo htmlspecialchars(
                $ogrenci["bolum"]
            );
            ?>

        </strong>


    </p>



    <!-- =====================================
         MESAJLAR
    ====================================== -->


    <?php if (isset($_GET["durum"])): ?>


        <?php if ($_GET["durum"] === "eklendi"): ?>


            <div class="basarili">

                Ders öğrenciye başarıyla atandı.

            </div>


        <?php elseif ($_GET["durum"] === "silindi"): ?>


            <div class="basarili">

                Öğrenci dersten başarıyla çıkarıldı.

            </div>


        <?php elseif ($_GET["durum"] === "zaten_var"): ?>


            <div class="uyari">

                Öğrenci zaten bu derse kayıtlı.

            </div>


        <?php elseif ($_GET["durum"] === "uygunsuz_ders"): ?>


            <div class="hata">

                Bu ders öğrencinin bölümüne ait değildir.
                Ders ataması yapılamadı.

            </div>


        <?php endif; ?>


    <?php endif; ?>



    <!-- =====================================
         DERS ATA
    ====================================== -->


    <div class="bolum">


        <h2>
            ➕ Öğrenciye Ders Ata
        </h2>


        <p
            style="
                color:#64748b;
                margin-bottom:15px;
            "
        >

            Yalnızca

            <strong>
                <?php
                echo htmlspecialchars(
                    $ogrenci["bolum"]
                );
                ?>
            </strong>

            bölümüne ait dersler gösterilmektedir.

        </p>



        <?php if (count($atanabilirDersler) > 0): ?>


            <form
                method="POST"
                class="ders-form"
            >


                <input
                    type="hidden"
                    name="islem"
                    value="ekle"
                >


                <select
                    name="dersKodu"
                    required
                >


                    <option value="">

                        Ders seçiniz

                    </option>


                    <?php foreach ($atanabilirDersler as $ders): ?>


                        <option
                            value="<?php
                            echo htmlspecialchars(
                                $ders["dersKodu"]
                            );
                            ?>"
                        >


                            <?php

                            echo htmlspecialchars(

                                $ders["dersKodu"]

                                . " - " .

                                $ders["dersAdi"]

                            );

                            ?>


                        </option>


                    <?php endforeach; ?>


                </select>


                <button
                    type="submit"
                    class="btn btn-ekle"
                >

                    Ders Ata

                </button>


            </form>


        <?php else: ?>


            <div class="bos">

                Bu öğrencinin bölümünde atanabilecek
                başka ders bulunmamaktadır.

            </div>


        <?php endif; ?>


    </div>



    <!-- =====================================
         ÖĞRENCİNİN ALDIĞI DERSLER
    ====================================== -->


    <div class="bolum">


        <h2>
            📖 Öğrencinin Aldığı Dersler
        </h2>



        <?php if (count($kayitliDersler) > 0): ?>


            <table>


                <thead>


                    <tr>

                        <th>Ders Kodu</th>

                        <th>Ders Adı</th>

                        <th>Bölüm</th>

                        <th>Akademisyen</th>

                        <th>İşlem</th>

                    </tr>


                </thead>


                <tbody>


                <?php foreach ($kayitliDersler as $ders): ?>


                    <tr>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $ders["dersKodu"]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $ders["dersAdi"]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $ders["bolum"]
                            );
                            ?>

                        </td>


                        <td>


                            <?php

                            if (!empty($ders["akademisyen"])) {

                                echo htmlspecialchars(
                                    $ders["akademisyen"]
                                );

                            } else {

                                echo "Atanmamış";
                            }

                            ?>


                        </td>


                        <td>


                            <form
                                method="POST"

                                onsubmit="
                                    return confirm(
                                        'Öğrenciyi bu dersten çıkarmak istediğinize emin misiniz?'
                                    );
                                "
                            >


                                <input
                                    type="hidden"
                                    name="islem"
                                    value="sil"
                                >


                                <input
                                    type="hidden"
                                    name="dersKodu"

                                    value="<?php
                                    echo htmlspecialchars(
                                        $ders["dersKodu"]
                                    );
                                    ?>"
                                >


                                <button
                                    type="submit"
                                    class="btn btn-sil"
                                >

                                    Dersten Çıkar

                                </button>


                            </form>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="bos">

                Bu öğrenci henüz herhangi bir
                derse kayıtlı değil.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>