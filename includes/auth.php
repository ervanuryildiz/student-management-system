<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function girisKontrol()
{
    if (!isset($_SESSION["rol"])) {
        header("Location: /student_project/login.php");
        exit;
    }
}

function rolKontrol($rol)
{
    girisKontrol();

    if ($_SESSION["rol"] !== $rol) {
        header("Location: /student_project/login.php");
        exit;
    }
}