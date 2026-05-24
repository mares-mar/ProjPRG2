<?php
session_start();

session_destroy();

header('Location: /sprava_rozpoctu/login.php');
exit();
?>