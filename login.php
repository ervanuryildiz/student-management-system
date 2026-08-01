<?php

session_start();

// Kullanıcı zaten giriş yapmışsa kendi paneline gönder
if (isset($_SESSION["rol"])) {

    if ($_SESSION["rol"] === "admin") {

        header("Location: admin/dashboard.php");
        exit;

    } elseif ($_SESSION["rol"] === "akademisyen") {

        header("Location: academicians/index.php");
        exit;

    } elseif ($_SESSION["rol"] === "ogrenci") {

        header("Location: student/dashboard.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Üniversite Öğrenci Takip Sistemi</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        body {

            background:
                radial-gradient(
                    circle at center,
                    #1e293b 0%,
                    #0f172a 100%
                );

            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            color: #fff;
        }

        .card {

            background: rgba(255, 255, 255, 0.95);

            border-radius: 12px;

            width: 380px;

            padding: 35px 30px;

            box-shadow:
                0 25px 50px -12px
                rgba(0, 0, 0, 0.5);

            text-align: center;

            color: #334155;
        }

        .logo-area {

            width: 60px;
            height: 60px;

            background: #1e3a8a;

            border-radius: 50%;

            margin: 0 auto 15px auto;

            display: flex;

            justify-content: center;
            align-items: center;

            color: #fff;

            font-size: 24px;
        }

        h2 {

            font-size: 18px;

            font-weight: 700;

            line-height: 1.3;

            margin-bottom: 10px;
        }

        h2 span {

            color: #0284c7;
        }

        .error-alert {

            background-color: #fee2e2;

            border: 1px solid #fca5a5;

            color: #991b1b;

            padding: 10px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;
        }

        .role-selector {

            display: flex;

            background: #f1f5f9;

            padding: 4px;

            border-radius: 8px;

            margin: 20px 0;

            border: 1px solid #e2e8f0;
        }

        .role-btn {

            flex: 1;

            padding: 10px;

            border: none;

            background: transparent;

            color: #64748b;

            font-weight: 600;

            font-size: 13px;

            cursor: pointer;

            border-radius: 6px;
        }

        .role-btn.active {

            background: #fff;

            color: #0f172a;

            box-shadow:
                0 2px 4px
                rgba(0,0,0,0.05);
        }

        .input-group {

            position: relative;

            margin-bottom: 15px;
        }

        .input-group span {

            position: absolute;

            left: 12px;

            top: 50%;

            transform: translateY(-50%);

            color: #94a3b8;
        }

        .input-group input {

            width: 100%;

            padding:
                12px
                12px
                12px
                38px;

            border:
                1px solid
                #cbd5e1;

            border-radius: 6px;

            font-size: 14px;

            outline: none;
        }

        .input-group input:focus {

            border-color: #0284c7;
        }

        .submit-btn {

            width: 100%;

            padding: 12px;

            background:
                linear-gradient(
                    to right,
                    #ea580c,
                    #0284c7
                );

            border: none;

            border-radius: 20px;

            color: white;

            font-weight: bold;

            cursor: pointer;

            margin-top: 10px;
        }

    </style>

</head>

<body>

<div class="card">

    <div class="logo-area">
        🎓
    </div>

    <h2>
        ÜNİVERSİTE ÖĞRENCİ
        <br>
        TAKİP SİSTEMİ |
        <span>GİRİŞ</span>
    </h2>


    <?php if (isset($_GET["hata"])): ?>

        <div class="error-alert">

            Kullanıcı adı veya şifre hatalı!

        </div>

    <?php endif; ?>


    <div class="role-selector">

        <button
            type="button"
            class="role-btn active"
            onclick="setRole(
                'ogrenci',
                'Öğrenci No',
                this
            )">

            Öğrenci

        </button>


        <button
            type="button"
            class="role-btn"
            onclick="setRole(
                'akademisyen',
                'Kullanıcı Adı',
                this
            )">

            Akademisyen / Admin

        </button>

    </div>


    <!-- ÖNEMLİ: login_control.php -->

    <form
        action="login_control.php"
        method="POST"
    >

        <input
            type="hidden"
            name="giris_tipi"
            id="giris_tipi"
            value="ogrenci"
        >


        <div class="input-group">

            <span>👤</span>

            <input
                type="text"
                name="kullanici"
                id="kullanici_input"
                placeholder="ÖĞRENCİ NO"
                required
                autocomplete="off"
            >

        </div>


        <div class="input-group">

            <span>🔒</span>

            <input
                type="password"
                name="sifre"
                placeholder="ŞİFRE"
                required
            >

        </div>


        <button
            type="submit"
            class="submit-btn"
        >

            GİRİŞ YAP

        </button>

    </form>

</div>


<script>

function setRole(
    role,
    placeholderText,
    element
) {

    document
        .getElementById('giris_tipi')
        .value = role;


    document
        .getElementById('kullanici_input')
        .placeholder =
            placeholderText.toUpperCase();


    const buttons =
        document.querySelectorAll(
            '.role-btn'
        );


    buttons.forEach(
        btn =>
            btn.classList.remove('active')
    );


    element.classList.add('active');
}

</script>

</body>

</html>