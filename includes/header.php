<?php

$adSoyad = $_SESSION["ad_soyad"] ?? "Kullanıcı";
$rol = $_SESSION["rol"] ?? "";

if ($rol === "admin") {

    $rolAdi = "Sistem Yöneticisi";

} elseif ($rol === "akademisyen") {

    $rolAdi = "Akademisyen";

} elseif ($rol === "ogrenci") {

    $rolAdi = "Öğrenci";

} else {

    $rolAdi = "Kullanıcı";
}

?>

<header class="top-header">

    <div class="header-title">

        <h2>
            Üniversite Öğrenci Takip Sistemi
        </h2>

        <p>
            <?php echo htmlspecialchars($rolAdi); ?> Paneli
        </p>

    </div>


    <div class="header-right">

        <!-- Daha sonra buraya bildirim zilini koyacağız -->

        <div class="profile">

            <div class="profile-text">

                <strong>
                    <?php echo htmlspecialchars($adSoyad); ?>
                </strong>

                <span>
                    <?php echo htmlspecialchars($rolAdi); ?>
                </span>

            </div>


            <div class="avatar">

                <?php

                echo htmlspecialchars(
                    mb_strtoupper(
                        mb_substr(
                            $adSoyad,
                            0,
                            1,
                            "UTF-8"
                        ),
                        "UTF-8"
                    )
                );

                ?>

            </div>

        </div>

    </div>

</header>