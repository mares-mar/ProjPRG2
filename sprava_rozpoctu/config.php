<?php
//definuji konstanty pro připojení
define("DB_host","localhost");
define("DB_user","root"); //defaultní přih. údaje root/ 
define("DB_pass","");
define("DB_name","home_budget");

//připojení k databázi
$conn = mysqli_connect(DB_host, DB_user, DB_pass, DB_name);


if (!$conn) {
    die('Chyba připojení: ' . mysqli_connect_error());
}

//nastavení znakové sady, mb4 umí líp ikony
mysqli_set_charset($conn, 'utf8mb4');
?>