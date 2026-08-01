<?php

session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /student_project/login.php");
    exit;
}

require_once __DIR__ . '/../../database.php';

$hata = "";
$fakulteAdi = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fakulteAdi = trim($_POST["fakulte"] ?? "");

    if ($fakulteAdi === "") {

        $hata = "Fakülte adı boş bırakılamaz.";

    } else {

        // Aynı fakülte var mı?
        $kontrol = $baglanti->prepare("
            SELECT COUNT(*)
            FROM fakulte
            WHERE `fakülte` = :fakulte
        ");

        $kontrol->execute([
            "fakulte" => $fakulteAdi
        ]);

        if ($kontrol->fetchColumn() > 0) {

            $hata = "Bu fakülte zaten sistemde kayıtlı.";

        } else {

            $stmt = $baglanti->prepare("
                INSERT INTO fakulte (`fakülte`)
                VALUES (:fakulte)
            ");

            $stmt->execute([
                "fakulte" => $fakulteAdi
            ]);

            header(
                "Location: /student_project/admin/faculties/index.php?durum=fakulte_eklendi"
            );

            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Fakülte Ekle</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f7fb;
    color: #1e293b;
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

.header h2 {
    margin: 0;
    font-size: 19px;
}

.header span {
    color: #94a3b8;
    font-size: 13px;
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

    box-shadow: 0 4px 12px rgba(0,0,0,.04);
}

.icon {
    width: 50px;
    height: 50px;

    background: #eff6ff;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    font-size: 24px;

    margin-bottom: 18px;
}

h1 {
    margin: 0 0 7px;
    color: #0f172a;
    font-size: 25px;
}

.description {
    color: #64748b;
    margin-bottom: 28px;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 7px;

    font-size: 13px;
    font-weight: 600;
}

input {
    width: 100%;
    height: 44px;

    padding: 0 13px;

    border: 1px solid #cbd5e1;
    border-radius: 8px;

    outline: none;

    font-size: 14px;
}

input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.08);
}

.actions {
    display: flex;
    gap: 10px;
}

.btn {
    border: none;

    padding: 11px 17px;

    border-radius: 8px;

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;

    text-decoration: none;
}

.btn-save {
    background: #2563eb;
    color: white;
}

.btn-save:hover {
    background: #1d4ed8;
}

.btn-back {
    background: #e2e8f0;
    color: #475569;
}

.error {
    background: #fee2e2;
    color: #991b1b;

    border: 1px solid #fecaca;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;
}

</style>

</head>

<body>

<div class="header">

    <h2>🎓 Öğrenci Takip Sistemi</h2>

    <span>
        <?php echo htmlspecialchars($_SESSION["ad_soyad"] ?? "Admin"); ?>
    </span>

</div>


<div class="container">

<div class="card">

    <div class="icon">🏫</div>

    <h1>Yeni Fakülte</h1>

    <p class="description">
        Sisteme yeni bir fakülte ekleyebilirsiniz.
    </p>


    <?php if ($hata !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($hata); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label>Fakülte Adı</label>

            <input
                type="text"
                name="fakulte"
                maxlength="150"
                value="<?php echo htmlspecialchars($fakulteAdi); ?>"
                placeholder="Örn: Mühendislik Fakültesi"
                required
            >

        </div>


        <div class="actions">

            <button
                type="submit"
                class="btn btn-save"
            >
                Fakülteyi Kaydet
            </button>

            <a
                href="/student_project/admin/faculties/index.php"
                class="btn btn-back"
            >
                Vazgeç
            </a>

        </div>

    </form>

</div>

</div>

</body>
</html>