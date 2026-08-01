<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';


$eskiFakulte = trim($_GET["fakulte"] ?? "");

if ($eskiFakulte === "") {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


// Fakülte var mı?
$stmt = $baglanti->prepare("
    SELECT *
    FROM fakulte
    WHERE `fakülte` = :fakulte
");

$stmt->execute([
    "fakulte" => $eskiFakulte
]);

$fakulte = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$fakulte) {

    header(
        "Location: /student_project/admin/faculties/index.php?durum=hata"
    );

    exit;
}


$hata = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $yeniFakulte = trim($_POST["fakulte"] ?? "");

    if ($yeniFakulte === "") {

        $hata = "Fakülte adı boş bırakılamaz.";

    } else {

        // Başka aynı isimli fakülte var mı?
        $kontrol = $baglanti->prepare("
            SELECT COUNT(*)
            FROM fakulte
            WHERE `fakülte` = :yeni
            AND `fakülte` <> :eski
        ");

        $kontrol->execute([
            "yeni" => $yeniFakulte,
            "eski" => $eskiFakulte
        ]);


        if ($kontrol->fetchColumn() > 0) {

            $hata = "Bu isimde başka bir fakülte zaten bulunuyor.";

        } else {

            try {

                $baglanti->beginTransaction();


                // Fakülteyi güncelle
                $stmt = $baglanti->prepare("
                    UPDATE fakulte
                    SET `fakülte` = :yeni
                    WHERE `fakülte` = :eski
                ");

                $stmt->execute([
                    "yeni" => $yeniFakulte,
                    "eski" => $eskiFakulte
                ]);


                // Bağlı bölümleri güncelle
                $stmtBolum = $baglanti->prepare("
                    UPDATE bolum
                    SET `fakülte` = :yeni
                    WHERE `fakülte` = :eski
                ");

                $stmtBolum->execute([
                    "yeni" => $yeniFakulte,
                    "eski" => $eskiFakulte
                ]);


                $baglanti->commit();


                header(
                    "Location: /student_project/admin/faculties/index.php?durum=guncellendi"
                );

                exit;


            } catch (PDOException $e) {

                if ($baglanti->inTransaction()) {
                    $baglanti->rollBack();
                }

                $hata = "Fakülte güncellenirken bir hata oluştu.";
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

<title>Fakülte Düzenle</title>

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

    padding: 0 35px;

    display: flex;
    align-items: center;
    justify-content: space-between;
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
    color: #0f172a;
    margin: 0 0 7px;
}

.description {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 7px;
}

input {
    width: 100%;
    height: 44px;

    border: 1px solid #cbd5e1;
    border-radius: 8px;

    padding: 0 13px;

    font-size: 14px;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 22px;
}

.btn {
    border: none;
    border-radius: 8px;

    padding: 11px 17px;

    text-decoration: none;

    font-weight: 600;
    font-size: 13px;

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

    <strong>🎓 Öğrenci Takip Sistemi</strong>

    <span>
        <?php echo htmlspecialchars($_SESSION["ad_soyad"] ?? "Admin"); ?>
    </span>

</div>


<div class="container">

<div class="card">

<h1>🏫 Fakülte Düzenle</h1>

<p class="description">
    Fakülte adını değiştirdiğinizde bağlı bölümler de
    otomatik olarak yeni fakülte adına taşınacaktır.
</p>


<?php if ($hata !== ""): ?>

<div class="error">
    <?php echo htmlspecialchars($hata); ?>
</div>

<?php endif; ?>


<form method="POST">

<label>Fakülte Adı</label>

<input
    type="text"
    name="fakulte"
    value="<?php echo htmlspecialchars(
        $_POST["fakulte"] ?? $fakulte["fakülte"]
    ); ?>"
    required
>


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