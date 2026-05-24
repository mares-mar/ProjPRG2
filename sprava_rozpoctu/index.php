<?php

session_start();

//pokud je uživatel přihlášený tak ho přesměruj rovnou na dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa domácího rozpočtu</title>

    <link rel="stylesheet" href="/sprava_rozpoctu/css/base.css">
    <link rel="stylesheet" href="/sprava_rozpoctu/css/landing.css">

</head>
<body>

<div class="landing-wrapper">

    <nav class="landing-nav">
        <div class="landing-nav-logo">Správa domácího rozpočtu</div>
        <div class="landing-nav-links">
            <a href="/sprava_rozpoctu/login.php" class="btn-landing-login">Přihlásit se</a>
            <a href="/sprava_rozpoctu/register.php" class="btn-landing-register">Registrovat se</a>
        </div>
    </nav>


    <div class="landing-hero">
        <h1 class="landing-title">Prevezměte kontrolu<br>nad rodinnými financemi</h1>
        <p class="landing-subtitle">
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. 
            Non nam temporibus numquam vel velit officia quis commodi nihil nulla consectetur.
        </p>
        <a href="/sprava_rozpoctu/register.php" class="btn-landing">
            Začít zdarma →
        </a>
    </div>

    <div class="landing-features">

        <div class="feature-card">
            <h3 class="feature-title">Dashboard a zadávání dat</h3>
            
            <p class="feature-text">
                Dashboard se základními informacemi.
                Zadávání příjmů a výdajů, rozdělení do kategorií.
            </p>
        </div>

        <div class="feature-card">
            <h3 class="feature-title">Grafy</h3>
            
            <p class="feature-text">
                V sekci statistiky jsou dva grafy, koláčový a sloupcový.
            </p>
        </div>

        <div class="feature-card">
            <h3 class="feature-title">Uživatelské účty</h3>
            
            <p class="feature-text">
                Každý člen rodiny si může vytvořit separátní uživatelský účet.
            </p>
        </div>

    </div>

    
    <div class="landing-footer">
        © 2026 All Rights Reserved. Program do Prog2.
    </div>

</div>
    
</body>
</html>