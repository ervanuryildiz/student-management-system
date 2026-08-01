<?php

session_start();


// ==========================================
// YETKİ KONTROLÜ
// ==========================================

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {

    header("Location: /student_project/login.php");
    exit;
}


// ==========================================
// VERİTABANI
// ==========================================

require_once __DIR__ . '/../../database.php';


// ==========================================
// DERS KODUNU AL
// ==========================================

$dersKodu = trim($_GET["dersKodu"] ?? "");

if ($dersKodu === "") {

    die("HATA: Ders kodu alınamadı.");
}


// ==========================================
// DERSİ GETİR
// ==========================================

try {

    $stmt = $baglanti->prepare("
        SELECT
            dersKodu,
            dersAdi,
            akademisyen

        FROM ders

        WHERE dersKodu = :dersKodu
    ");

    $stmt->execute([
        "dersKodu" => $dersKodu
    ]);

    $ders = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$ders) {

        die(
            "HATA: Ders bulunamadı. Ders kodu: "
            . htmlspecialchars($dersKodu)
        );
    }

} catch (PDOException $e) {

    die(
        "Ders getirilirken hata oluştu: "
        . htmlspecialchars($e->getMessage())
    );
}


// ==========================================
// DEĞİŞKENLER
// ==========================================

$hata = "";


// ==========================================
// ÖĞRENCİ EKLEME
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ogrenciNo = trim($_POST["ogrenciNo"] ?? "");


    // Öğrenci seçilmiş mi?

    if ($ogrenciNo === "") {

        $hata = "Lütfen bir öğrenci seçiniz.";

    } else {

        try {


            // ==========================================
            // ÖĞRENCİ VAR MI?
            // ==========================================

            $stmt = $baglanti->prepare("
                SELECT COUNT(*)

                FROM ogrenci

                WHERE ogrenciNo = :ogrenciNo
            ");

            $stmt->execute([
                "ogrenciNo" => $ogrenciNo
            ]);


            if ($stmt->fetchColumn() == 0) {

                $hata =
                    "Seçilen öğrenci sistemde bulunamadı.";

            } else {


                // ==========================================
                // ÖĞRENCİ ZATEN BU DERSTE Mİ?
                // ==========================================

                $stmt = $baglanti->prepare("
                    SELECT COUNT(*)

                    FROM ogrenci_ders

                    WHERE ogrenciNo = :ogrenciNo
                    AND dersKodu = :dersKodu
                ");

                $stmt->execute([

                    "ogrenciNo" => $ogrenciNo,

                    "dersKodu" => $dersKodu

                ]);


                if ($stmt->fetchColumn() > 0) {

                    $hata =
                        "Bu öğrenci zaten bu derse kayıtlı.";

                } else {


                    // ==========================================
                    // ÖĞRENCİYİ DERSE EKLE
                    // ==========================================

                    $stmt = $baglanti->prepare("
                        INSERT INTO ogrenci_ders
                        (
                            ogrenciNo,
                            dersKodu
                        )

                        VALUES
                        (
                            :ogrenciNo,
                            :dersKodu
                        )
                    ");


                    $stmt->execute([

                        "ogrenciNo" => $ogrenciNo,

                        "dersKodu" => $dersKodu

                    ]);


                    // ==========================================
                    // BAŞARILI
                    // ==========================================

                    header(
                        "Location: /student_project/admin/courses/ogrenciler.php"
                        . "?dersKodu="
                        . urlencode($dersKodu)
                        . "&durum=ogrenci_eklendi"
                    );

                    exit;
                }
            }


        } catch (PDOException $e) {

            // Hata olursa gerçek SQL hatasını göster.
            // Proje tamamlandığında bu teknik mesaj kaldırılabilir.

            $hata =
                "VERİTABANI HATASI: "
                . $e->getMessage();
        }
    }
}


// ==========================================
// DERSTE OLMAYAN ÖĞRENCİLERİ GETİR
// ==========================================

try {

    $stmt = $baglanti->prepare("
        SELECT

            o.ogrenciNo,

            o.ad,

            o.soyad,

            o.bolum

        FROM ogrenci o

        WHERE NOT EXISTS
        (
            SELECT 1

            FROM ogrenci_ders od

            WHERE od.ogrenciNo = o.ogrenciNo

            AND od.dersKodu = :dersKodu
        )

        ORDER BY
            o.ad ASC,
            o.soyad ASC
    ");


    $stmt->execute([
        "dersKodu" => $dersKodu
    ]);


    $ogrenciler =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    die(
        "Öğrenciler getirilirken hata oluştu: "
        . htmlspecialchars($e->getMessage())
    );
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

<title>Derse Öğrenci Ekle</title>


<style>


/* ==========================================
   GENEL
========================================== */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;
}


body {

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background-color: #f4f7fb;

    color: #1e293b;

    min-height: 100vh;
}



/* ==========================================
   HEADER
========================================== */

.header {

    min-height: 72px;

    background-color: #0f172a;

    color: white;

    padding: 15px 40px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;
}


.header-left h2 {

    font-size: 19px;

    margin-bottom: 4px;
}


.header-left p {

    color: #94a3b8;

    font-size: 13px;
}


.header-user {

    text-align: right;
}


.header-user span {

    display: block;

    color: #94a3b8;

    font-size: 12px;

    margin-bottom: 3px;
}


.header-user strong {

    font-size: 14px;
}



/* ==========================================
   ANA CONTAINER
========================================== */

.container {

    max-width: 800px;

    margin: 40px auto;

    padding: 0 20px;
}



/* ==========================================
   GERİ BUTONU
========================================== */

.top-menu {

    margin-bottom: 18px;
}


.btn-back {

    display: inline-block;

    padding: 9px 14px;

    background-color: #64748b;

    color: white;

    text-decoration: none;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;
}


.btn-back:hover {

    background-color: #475569;
}



/* ==========================================
   DERS KARTI
========================================== */

.course-card {

    background:
        linear-gradient(
            135deg,
            #0f172a,
            #1e293b
        );

    color: white;

    border-radius: 14px;

    padding: 25px;

    margin-bottom: 20px;

    box-shadow:
        0 6px 18px rgba(15,23,42,.12);
}


.course-code {

    display: inline-block;

    background-color:
        rgba(59,130,246,.18);

    color: #93c5fd;

    padding: 5px 9px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 10px;
}


.course-card h1 {

    font-size: 24px;

    margin-bottom: 10px;
}


.course-info {

    color: #cbd5e1;

    font-size: 14px;
}


.course-info strong {

    color: white;
}



/* ==========================================
   FORM KARTI
========================================== */

.card {

    background-color: white;

    border: 1px solid #e2e8f0;

    border-radius: 14px;

    padding: 30px;

    box-shadow:
        0 4px 12px rgba(0,0,0,.04);
}


.card-header {

    margin-bottom: 25px;
}


.card-header h2 {

    color: #0f172a;

    font-size: 22px;

    margin-bottom: 7px;
}


.card-header p {

    color: #64748b;

    font-size: 14px;

    line-height: 1.6;
}



/* ==========================================
   HATA
========================================== */

.error {

    background-color: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

    padding: 14px;

    border-radius: 8px;

    margin-bottom: 22px;

    font-size: 14px;

    line-height: 1.5;

    word-break: break-word;
}



/* ==========================================
   FORM
========================================== */

.form-group {

    margin-bottom: 20px;
}


label {

    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: 600;

    color: #334155;
}


select {

    width: 100%;

    height: 48px;

    padding: 0 13px;

    background-color: white;

    border: 1px solid #cbd5e1;

    border-radius: 8px;

    color: #1e293b;

    font-size: 14px;

    transition: .2s;
}


select:hover {

    border-color: #94a3b8;
}


select:focus {

    outline: none;

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,.1);
}



/* ==========================================
   BUTONLAR
========================================== */

.actions {

    display: flex;

    gap: 10px;

    margin-top: 25px;

    flex-wrap: wrap;
}


.btn {

    display: inline-block;

    border: none;

    border-radius: 8px;

    padding: 11px 18px;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;

    font-weight: 600;

    transition: .2s;
}


.btn-save {

    background-color: #16a34a;

    color: white;
}


.btn-save:hover {

    background-color: #15803d;
}


.btn-cancel {

    background-color: #e2e8f0;

    color: #475569;
}


.btn-cancel:hover {

    background-color: #cbd5e1;
}



/* ==========================================
   BOŞ ÖĞRENCİ
========================================== */

.empty {

    background-color: #f8fafc;

    border: 1px dashed #cbd5e1;

    padding: 30px;

    border-radius: 10px;

    text-align: center;

    color: #64748b;
}


.empty-icon {

    font-size: 32px;

    margin-bottom: 10px;
}


.empty strong {

    display: block;

    color: #334155;

    margin-bottom: 5px;
}



/* ==========================================
   BİLGİ
========================================== */

.info {

    background-color: #eff6ff;

    border: 1px solid #bfdbfe;

    color: #1e40af;

    padding: 12px 14px;

    border-radius: 8px;

    font-size: 13px;

    margin-top: 20px;

    line-height: 1.5;
}



/* ==========================================
   MOBİL
========================================== */

@media (max-width: 650px) {

    .header {

        padding: 15px 20px;

        align-items: flex-start;

        flex-direction: column;
    }


    .header-user {

        text-align: left;
    }


    .container {

        margin-top: 25px;
    }


    .card {

        padding: 22px;
    }


    .actions {

        flex-direction: column;
    }


    .btn {

        width: 100%;

        text-align: center;
    }
}


</style>

</head>


<body>


<!-- ==========================================
     HEADER
========================================== -->

<div class="header">


    <div class="header-left">

        <h2>
            🎓 Üniversite Öğrenci Takip Sistemi
        </h2>

        <p>
            Ders ve öğrenci yönetimi
        </p>

    </div>


    <div class="header-user">

        <span>
            Yönetici
        </span>

        <strong>

            <?php

            echo htmlspecialchars(
                $_SESSION["ad_soyad"] ?? ""
            );

            ?>

        </strong>

    </div>


</div>



<!-- ==========================================
     ANA İÇERİK
========================================== -->

<div class="container">


    <!-- GERİ -->

    <div class="top-menu">

        <a
            href="/student_project/admin/courses/ogrenciler.php?dersKodu=<?php
            echo urlencode($dersKodu);
            ?>"
            class="btn-back"
        >

            ← Ders Öğrencilerine Dön

        </a>

    </div>



    <!-- ======================================
         DERS BİLGİSİ
    ======================================= -->

    <div class="course-card">


        <div class="course-code">

            <?php

            echo htmlspecialchars(
                $ders["dersKodu"]
            );

            ?>

        </div>


        <h1>

            <?php

            echo htmlspecialchars(
                $ders["dersAdi"]
            );

            ?>

        </h1>


        <div class="course-info">

            Akademisyen:

            <strong>

                <?php

                if (!empty($ders["akademisyen"])) {

                    echo htmlspecialchars(
                        $ders["akademisyen"]
                    );

                } else {

                    echo "Henüz atanmadı";
                }

                ?>

            </strong>

        </div>


    </div>



    <!-- ======================================
         FORM
    ======================================= -->

    <div class="card">


        <div class="card-header">

            <h2>
                👨‍🎓 Derse Öğrenci Ekle
            </h2>


            <p>

                Bu derse kaydetmek istediğiniz öğrenciyi
                aşağıdaki listeden seçiniz.

                Derse daha önce kayıtlı öğrenciler
                listede gösterilmez.

            </p>

        </div>



        <!-- ==================================
             HATA MESAJI
        =================================== -->

        <?php if ($hata !== ""): ?>


            <div class="error">

                <strong>
                    İşlem gerçekleştirilemedi
                </strong>

                <br><br>

                <?php

                echo htmlspecialchars(
                    $hata
                );

                ?>

            </div>


        <?php endif; ?>



        <!-- ==================================
             ÖĞRENCİ VARSA
        =================================== -->

        <?php if (count($ogrenciler) > 0): ?>


            <form
                method="POST"

                action="/student_project/admin/courses/ogrenci_ekle.php?dersKodu=<?php
                echo urlencode($dersKodu);
                ?>"
            >


                <div class="form-group">


                    <label for="ogrenciNo">

                        Öğrenci Seçiniz

                    </label>


                    <select
                        name="ogrenciNo"
                        id="ogrenciNo"
                        required
                    >


                        <option value="">

                            -- Öğrenci seçiniz --

                        </option>



                        <?php foreach ($ogrenciler as $ogrenci): ?>


                            <option

                                value="<?php

                                echo htmlspecialchars(
                                    $ogrenci["ogrenciNo"]
                                );

                                ?>"

                                <?php

                                if (
                                    isset($_POST["ogrenciNo"])
                                    &&
                                    $_POST["ogrenciNo"]
                                        == $ogrenci["ogrenciNo"]
                                ) {

                                    echo "selected";
                                }

                                ?>

                            >


                                <?php


                                echo htmlspecialchars(

                                    $ogrenci["ogrenciNo"]

                                    . " - "

                                    . $ogrenci["ad"]

                                    . " "

                                    . $ogrenci["soyad"]

                                    . " - "

                                    . $ogrenci["bolum"]

                                );


                                ?>


                            </option>


                        <?php endforeach; ?>


                    </select>


                </div>



                <div class="actions">


                    <button
                        type="submit"
                        class="btn btn-save"
                    >

                        ✓ Öğrenciyi Derse Ekle

                    </button>


                    <a

                        href="/student_project/admin/courses/ogrenciler.php?dersKodu=<?php
                        echo urlencode($dersKodu);
                        ?>"

                        class="btn btn-cancel"

                    >

                        Vazgeç

                    </a>


                </div>


            </form>



            <div class="info">

                ℹ️ Listede yalnızca
                <strong>
                    <?php echo count($ogrenciler); ?>
                </strong>
                adet bu derse henüz kayıtlı olmayan
                öğrenci gösteriliyor.

            </div>



        <?php else: ?>


            <!-- ==================================
                 EKLENEBİLECEK ÖĞRENCİ YOK
            =================================== -->

            <div class="empty">


                <div class="empty-icon">
                    👨‍🎓
                </div>


                <strong>
                    Eklenebilecek öğrenci bulunamadı.
                </strong>


                <p>

                    Sistemdeki tüm öğrenciler bu derse
                    zaten kayıtlı olabilir.

                </p>


            </div>


            <div class="actions">


                <a

                    href="/student_project/admin/courses/ogrenciler.php?dersKodu=<?php
                    echo urlencode($dersKodu);
                    ?>"

                    class="btn btn-cancel"

                >

                    ← Ders Öğrencilerine Dön

                </a>


            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>