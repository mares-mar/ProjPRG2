<?php
session_start();
require_once 'config.php';


if (isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/dashboard.php');
    exit();
}

$error_message   = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST["username"]); //trim odstraní mezeru za nebo před stringem
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Vyplňte prosím všechna pole.";

    } else if (strlen($password) < 6) {
        $error_message = "Heslo musí mít alespoň 6 znaků.";

    } else if ($password !== $password_confirm) {
        $error_message = "Hesla se neshodují.";
    
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Zadejte platný email.";

    } else {
        //ochrana proti sql injection znakům, ikdyž dobrá ale nevím jak to líp vyřešit
        //https://stackoverflow.com/questions/32391315/is-mysqli-real-escape-string-enough-to-avoid-sql-injection-or-other-sql-attack
        $username = mysqli_real_escape_string($conn, $username);
        $email    = mysqli_real_escape_string($conn, $email);

        
        $check_query  = "SELECT id FROM users WHERE email = '$email'";
        $check_email = mysqli_query($conn, $check_query);

        
        if(mysqli_num_rows($check_email) > 0){
            $error_message = "Registraci se nepodařilo dokončit. Zkontrolujte zadané údaje.";
        } else {

            // hashování hesla, Password_default používá bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
            $stmt->bind_param('sss', $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registrace byla úspěšná. Nyní se můžete přihlásit.";
            } else {
                $error_message = "Někde se stala chyba";
            }
            $stmt->close();
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

        <h1 class="auth-title">Vytvořit účet</h1>
        
        <?php if ($success_message !== '') { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } ?>

        <?php if ($error_message !== '') { ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Uživatelské jméno</label>
                <input type="text" name="username" id="username" placeholder="Vaše jméno"
                       value="<?php if (isset($_POST['username'])) { echo $_POST['username']; } ?>">
            
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="vas@email.cz"
                       value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } ?>">
            
            </div>

            <div class="form-group">
                <label for="password">Heslo</label>
                <input type="password" name="password" id="password" placeholder="Minimálně 6 znaků">

            </div>

            <div class="form-group">
                <label for="password_confirm">Potvrdit heslo</label>
                <input type="password" name="password_confirm" id="password_confirm" placeholder="Zopakujte heslo">
            </div>

            <button type="submit" class="btn-auth">Zaregistrovat se</button>

        </form>

        <div class="auth-switch">
            Již máte účet? <a href="/sprava_rozpoctu/login.php">Přihlaste se</a>
        </div>

    </div>
</div>

</body>
</html>