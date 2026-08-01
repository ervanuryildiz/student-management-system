<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$eskiBolum =
    trim($_GET["bolum"] ?? "");

$eskiFakulte =
    trim($_GET["fakulte"] ?? "");


if ($eskiBolum === "" || $eskiFakulte === "") {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// BÖLÜMÜ GETİR
// ==========================================

$stmt = $baglanti->prepare("
    SELECT *
    FROM bolum
    WHERE `bölüm` = :bolum
    AND `fakülte` = :fakulte
");

$stmt->execute([
    "bolum" => $eskiBolum,
    "fakulte" => $eskiFakulte
]);

$bolum = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$bolum) {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// ==========================================
// FAKÜLTELER
// ==========================================

$stmtFakulte = $baglanti->query("
    SELECT `fakülte`
    FROM fakulte
    ORDER BY `fakülte` ASC
");

$fakulteler =
    $stmtFakulte->fetchAll(PDO::FETCH_ASSOC);


$hata = "";


// ==========================================
// GÜNCELLE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $yeniBolum =
        trim($_POST["bolum"] ?? "");

    $yeniFakulte =
        trim($_POST["fakulte"] ?? "");


    if ($yeniBolum === "" || $yeniFakulte === "") {

        $hata =
            "Bölüm ve fakülte bilgileri zorunludur.";

    } else {

        // Fakülte gerçekten var mı?
        $kontrolFakulte = $baglanti->prepare("
            SELECT COUNT(*)
            FROM fakulte
            WHERE `fakülte` = :fakulte
        ");

        $kontrolFakulte->execute([
            "fakulte" => $yeniFakulte
        ]);


        if ($kontrolFakulte->fetchColumn() == 0) {

            $hata =
                "Seçilen fakülte bulunamadı.";

        } else {

            // Aynı kayıt başka yerde var mı?
            $kontrol = $baglanti->prepare("
                SELECT COUNT(*)
                FROM bolum

                WHERE `bölüm` = :yeniBolum
                AND `fakülte` = :yeniFakulte

                AND NOT (
                    `bölüm` = :eskiBolum
                    AND `fakülte` = :eskiFakulte
                )
            ");

            $kontrol->execute([
                "yeniBolum" => $yeniBolum,
                "yeniFakulte" => $yeniFakulte,
                "eskiBolum" => $eskiBolum,
                "eskiFakulte" => $eskiFakulte
            ]);


            if ($kontrol->fetchColumn() > 0) {

                $hata =
                    "Bu bölüm seçilen fakültede zaten bulunuyor.";

            } else {

                $stmt = $baglanti->prepare("
                    UPDATE bolum

                    SET
                        `bölüm` = :yeniBolum,
                        `fakülte` = :yeniFakulte

                    WHERE
                        `bölüm` = :eskiBolum
                        AND `fakülte` = :eskiFakulte
                ");

                $stmt->execute([
                    "yeniBolum" => $yeniBolum,
                    "yeniFakulte" => $yeniFakulte,
                    "eskiBolum" => $eskiBolum,
                    "eskiFakulte" => $eskiFakulte
                ]);


                header(
                    "Location: /student_project/admin/faculties/index.php?durum=guncellendi"
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

<title>Bölüm Düzenle</title>

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
    max-width: 650px;

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

    color: #0f172a;
}

.description {
    color: #64748b;

    font-size: 14px;

    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 19px;
}

label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 600;
}

input,
select {
    width: 100%;

    height: 44px;

    border: 1px solid #cbd5e1;

    border-radius: 8px;

    padding: 0 13px;

    background: white;

    font-size: 14px;
}

.actions {
    display: flex;

    gap: 10px;

    margin-top: 22px;
}

.btn {
    padding: 11px 17px;

    border: none;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;
}

.save {
    background: #7c3aed;
    color: white;
}

.back {
    background: #e2e8f0;
    color: #475569;
}

.error {
    padding: 12px;

    background: #fee2e2;

    color: #991b1b;

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
    $_SESSION["ad_soyad"] ?? "Admin"
);
?>
</span>

</div>


<div class="container">

<div class="card">


<h1>🎓 Bölüm Düzenle</h1>

<p class="description">
    Bölümün adını veya bağlı olduğu fakülteyi değiştirebilirsiniz.
</p>


<?php if ($hata !== ""): ?>

<div class="error">
    <?php echo htmlspecialchars($hata); ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
    Bölüm Adı
</label>

<input
    type="text"
    name="bolum"
    value="<?php
    echo htmlspecialchars(
        $_POST["bolum"] ?? $bolum["bölüm"]
    );
    ?>"
    required
>

</div>


<div class="form-group">

<label>
    Fakülte
</label>


<select
    name="fakulte"
    required
>


<?php

$aktifFakulte =
    $_POST["fakulte"]
    ?? $bolum["fakülte"];

?>


<?php foreach ($fakulteler as $fakulte): ?>


<option
    value="<?php
    echo htmlspecialchars(
        $fakulte["fakülte"]
    );
    ?>"

    <?php
    echo $aktifFakulte === $fakulte["fakülte"]
        ? "selected"
        : "";
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


<div class="actions">


<button
    type="submit"
    class="btn save"
>
    Değişiklikleri Kaydet
</button>


<a
    href="/student_project/admin/faculties/index.php"
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