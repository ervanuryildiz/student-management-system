<?php

session_start();

// Sadece admin erişebilir
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}

require_once '../../database.php';

$hata = "";


// --------------------------------------
// FAKÜLTELERİ VERİTABANINDAN GETİR
// --------------------------------------

$sqlFakulte = "
    SELECT *
    FROM fakulte
    ORDER BY `fakülte` ASC
";

$stmtFakulte = $baglanti->prepare($sqlFakulte);

$stmtFakulte->execute();

$fakulteler = $stmtFakulte->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------
// FORM GÖNDERİLDİĞİNDE
// --------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $bolumAdi = trim($_POST["bolumAdi"] ?? "");

    $fakulteAdi = trim($_POST["fakulteAdi"] ?? "");


    // Boş alan kontrolü

    if (empty($bolumAdi) || empty($fakulteAdi)) {

        $hata = "Lütfen bölüm ve fakülte bilgilerini doldurunuz.";

    } else {


        // --------------------------------------
        // AYNI BÖLÜM DAHA ÖNCE EKLENMİŞ Mİ?
        // --------------------------------------

        $kontrolSql = "
            SELECT *
            FROM bolum
            WHERE `bölüm` = :bolumAdi
            AND `fakülte` = :fakulteAdi
        ";


        $kontrolStmt = $baglanti->prepare($kontrolSql);


        $kontrolStmt->execute([

            "bolumAdi" => $bolumAdi,

            "fakulteAdi" => $fakulteAdi

        ]);


        $bolumVarMi = $kontrolStmt->fetch(PDO::FETCH_ASSOC);


        if ($bolumVarMi) {

            $hata = "Bu bölüm seçilen fakültede zaten kayıtlı.";

        } else {


            // --------------------------------------
            // BÖLÜMÜ EKLE
            // --------------------------------------

            $sql = "
                INSERT INTO bolum
                (
                    `bölüm`,
                    `fakülte`
                )

                VALUES
                (
                    :bolumAdi,
                    :fakulteAdi
                )
            ";


            $stmt = $baglanti->prepare($sql);


            $sonuc = $stmt->execute([

                "bolumAdi" => $bolumAdi,

                "fakulteAdi" => $fakulteAdi

            ]);


            if ($sonuc) {

                header("Location: index.php?durum=bolum_eklendi");
                exit;

            } else {

                $hata = "Bölüm eklenirken bir hata oluştu.";

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="tr">


<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Bölüm Ekle</title>


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

            max-width: 600px;

            margin: auto;

            background-color: white;

            padding: 30px;

            border-radius: 10px;

        }


        h1 {

            color: #1e293b;

            margin-top: 0;

            margin-bottom: 25px;

        }


        .form-group {

            margin-bottom: 20px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #334155;

        }


        input,
        select {

            width: 100%;

            padding: 11px;

            border: 1px solid #cbd5e1;

            border-radius: 5px;

            font-size: 14px;

        }


        input:focus,
        select:focus {

            outline: none;

            border-color: #0284c7;

        }


        .butonlar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 25px;

        }


        .btn {

            display: inline-block;

            padding: 10px 15px;

            border: none;

            border-radius: 5px;

            color: white;

            text-decoration: none;

            cursor: pointer;

        }


        .btn-ekle {

            background-color: #16a34a;

        }


        .btn-geri {

            background-color: #64748b;

        }


        .hata {

            background-color: #fee2e2;

            color: #991b1b;

            padding: 12px;

            margin-bottom: 20px;

            border-radius: 5px;

        }


        .uyari {

            background-color: #fef3c7;

            color: #92400e;

            padding: 12px;

            margin-bottom: 20px;

            border-radius: 5px;

        }

    </style>


</head>


<body>


<div class="container">


    <h1>Yeni Bölüm Ekle</h1>


    <!-- HATA MESAJI -->

    <?php if (!empty($hata)): ?>

        <div class="hata">

            <?php echo htmlspecialchars($hata); ?>

        </div>

    <?php endif; ?>



    <!-- FAKÜLTE YOKSA BÖLÜM EKLENEMEZ -->

    <?php if (count($fakulteler) == 0): ?>


        <div class="uyari">

            Bölüm ekleyebilmek için önce sisteme bir fakülte eklemelisiniz.

        </div>


        <a
            href="fakulte_ekle.php"
            class="btn btn-ekle"
        >

            + Fakülte Ekle

        </a>


        <a
            href="index.php"
            class="btn btn-geri"
        >

            ← Geri Dön

        </a>


    <?php else: ?>


        <!-- BÖLÜM EKLEME FORMU -->


        <form method="POST">


            <div class="form-group">


                <label for="bolumAdi">

                    Bölüm Adı

                </label>


                <input
                    type="text"
                    id="bolumAdi"
                    name="bolumAdi"
                    placeholder="Örn: Bilgisayar Mühendisliği"
                    value="<?php
                        echo isset($_POST["bolumAdi"])
                            ? htmlspecialchars($_POST["bolumAdi"])
                            : "";
                    ?>"
                    required
                >


            </div>



            <div class="form-group">


                <label for="fakulteAdi">

                    Fakülte

                </label>


                <select
                    name="fakulteAdi"
                    id="fakulteAdi"
                    required
                >


                    <option value="">

                        Fakülte Seçiniz

                    </option>


                    <?php foreach ($fakulteler as $fakulte): ?>


                        <option
                            value="<?php echo htmlspecialchars($fakulte["fakülte"]); ?>"

                            <?php

                            if (
                                isset($_POST["fakulteAdi"]) &&
                                $_POST["fakulteAdi"] === $fakulte["fakülte"]
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $fakulte["fakülte"]
                            );
                            ?>

                        </option>


                    <?php endforeach; ?>


                </select>


            </div>



            <div class="butonlar">


                <a
                    href="index.php"
                    class="btn btn-geri"
                >

                    ← Geri Dön

                </a>


                <button
                    type="submit"
                    class="btn btn-ekle"
                >

                    Bölüm Ekle

                </button>


            </div>


        </form>


    <?php endif; ?>


</div>


</body>


</html>