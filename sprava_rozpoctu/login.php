<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = 'Vyplňte prosím všechna pole.';

    } else {

        $email = mysqli_real_escape_string($conn, $email);

        // Najde uživatele podle emailu
        $query  = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            // porovnání hesla 
            if (password_verify($password, $user['password'])) {

                // ukládání user info do sessiony
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];

                header('Location: /sprava_rozpoctu/dashboard.php');
                exit();
            } else {
                $error_message = 'Špatný email nebo heslo.';
            }
        } else {
            $error_message = 'Špatný email nebo heslo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa domácího rozpočtu</title>

    <link rel="stylesheet" href="/sprava_rozpoctu/css/base.css">
    <link rel="stylesheet" href="/sprava_rozpoctu/css/auth.css">

</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <h1 class="auth-title">Přihlášení</h1>

        <?php if ($error_message !== '') { ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="vas@email.cz"
                       value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } ?>">
            </div>

            <div class="form-group">
                <label for="password">Heslo</label>
                <input type="password" name="password" id="password" placeholder="Vaše heslo">

            </div>

            <button type="submit" class="btn-auth">Přihlásit se</button>
        </form>

        <div class="auth-switch">
            Nemáte účet? <a href="/sprava_rozpoctu/register.php">Zaregistrujte se</a>

        </div>

    </div>
</div>

</body>
</html>
