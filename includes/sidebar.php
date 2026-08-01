<?php

$rol = $_SESSION["rol"] ?? "";
$currentPage = $_SERVER["PHP_SELF"] ?? "";


// ==========================================
// ACTIVE KONTROLÜ
// ==========================================

function menuActive($aranan)
{
    global $currentPage;

    return strpos(
        $currentPage,
        $aranan
    ) !== false
        ? "active"
        : "";
}

?>


<aside class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        <div class="logo-icon">
            🎓
        </div>

        <div class="logo-text">

            <h2>
                Öğrenci Takip
            </h2>

            <span>
                Yönetim Sistemi
            </span>

        </div>

    </div>


    <!-- ======================================
         ADMIN MENÜSÜ
    ======================================= -->

    <?php if ($rol === "admin"): ?>


        <div class="menu-title">
            Yönetim
        </div>


        <nav class="sidebar-menu">


            <a
                href="/student_project/admin/dashboard.php"
                class="<?php
                echo menuActive(
                    "/admin/dashboard.php"
                );
                ?>"
            >

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="/student_project/admin/students/index.php"
                class="<?php
                echo menuActive(
                    "/admin/students/"
                );
                ?>"
            >

                <span class="menu-icon">
                    👥
                </span>

                <span>
                    Öğrenciler
                </span>

            </a>


            <a
                href="/student_project/admin/academicians/index.php"
                class="<?php
                echo menuActive(
                    "/admin/academicians/"
                );
                ?>"
            >

                <span class="menu-icon">
                    👨‍🏫
                </span>

                <span>
                    Akademisyenler
                </span>

            </a>


            <a
                href="/student_project/admin/courses/index.php"
                class="<?php
                echo menuActive(
                    "/admin/courses/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📚
                </span>

                <span>
                    Dersler
                </span>

            </a>


            <a
                href="/student_project/admin/announcements/index.php"
                class="<?php
                echo menuActive(
                    "/admin/announcements/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📢
                </span>

                <span>
                    Duyurular
                </span>

            </a>


        </nav>


    <!-- ======================================
         AKADEMİSYEN MENÜSÜ
    ======================================= -->

    <?php elseif ($rol === "akademisyen"): ?>


        <div class="menu-title">
            Akademisyen
        </div>


        <nav class="sidebar-menu">


            <a
                href="/student_project/academician/dashboard.php"
                class="<?php
                echo menuActive(
                    "/academician/dashboard.php"
                );
                ?>"
            >

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="/student_project/academician/courses/index.php"
                class="<?php
                echo menuActive(
                    "/academician/courses/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📚
                </span>

                <span>
                    Derslerim
                </span>

            </a>


            <a
                href="/student_project/academician/grades/index.php"
                class="<?php
                echo menuActive(
                    "/academician/grades/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📝
                </span>

                <span>
                    Not İşlemleri
                </span>

            </a>


            <a
                href="/student_project/academician/announcements/index.php"
                class="<?php
                echo menuActive(
                    "/academician/announcements/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📢
                </span>

                <span>
                    Duyurular
                </span>

            </a>


            <a
                href="/student_project/academician/password/index.php"
                class="<?php
                echo menuActive(
                    "/academician/password/"
                );
                ?>"
            >

                <span class="menu-icon">
                    🔒
                </span>

                <span>
                    Şifre Değiştir
                </span>

            </a>


        </nav>


    <!-- ======================================
         ÖĞRENCİ MENÜSÜ
    ======================================= -->

    <?php elseif ($rol === "ogrenci"): ?>


        <div class="menu-title">
            Öğrenci
        </div>


        <nav class="sidebar-menu">


            <a
                href="/student_project/student/dashboard.php"
                class="<?php
                echo menuActive(
                    "/student/dashboard.php"
                );
                ?>"
            >

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="/student_project/student/courses/index.php"
                class="<?php
                echo menuActive(
                    "/student/courses/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📚
                </span>

                <span>
                    Derslerim
                </span>

            </a>


            <a
                href="/student_project/student/grades/index.php"
                class="<?php
                echo menuActive(
                    "/student/grades/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📝
                </span>

                <span>
                    Notlarım
                </span>

            </a>


            <a
                href="/student_project/student/announcements/index.php"
                class="<?php
                echo menuActive(
                    "/student/announcements/"
                );
                ?>"
            >

                <span class="menu-icon">
                    📢
                </span>

                <span>
                    Duyurular
                </span>

            </a>


            <a
                href="/student_project/student/password/index.php"
                class="<?php
                echo menuActive(
                    "/student/password/"
                );
                ?>"
            >

                <span class="menu-icon">
                    🔒
                </span>

                <span>
                    Şifre Değiştir
                </span>

            </a>


        </nav>


    <?php endif; ?>


    <!-- ======================================
         ÇIKIŞ
    ======================================= -->

    <div class="sidebar-bottom">

       <a
    href="/student_project/logout.php"
    class="logout-sidebar"
>
    Çıkış Yap
</a>

    </div>


</aside>