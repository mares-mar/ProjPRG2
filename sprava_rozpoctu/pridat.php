<?php
session_start();
$active_page = 'pridat';

if (!isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/login.php');
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];

$success_message = '';
$error_message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type        = $_POST['type'];
    $category    = $_POST['category'];
    $amount      = $_POST['amount'];
    $description = $_POST['description'];
    $date        = $_POST['date'];

    if (empty($type) || empty($category) || empty($amount) || empty($date)) {
        $error_message = 'Vyplňte prosím všechna povinná pole.';
    } else {

        $allowed_types = ["income", "expense"];

        if(!in_array($type, $allowed_types, true)){
            $error_message = "Neplatný typ transakce";
        }
        else {
            $amount = floatval($amount);

        if ($amount <= 0) {
            $error_message = 'Částka musí být větší než nula.';
        } else {

            $description = mysqli_real_escape_string($conn, $description);
            $category    = mysqli_real_escape_string($conn, $category);

            $stmt = $conn->prepare(
                "INSERT INTO transactions (user_id, type, category, amount, description, date)
                VALUES (?,?,?,?,?,?)"
            );
            if ($stmt){
                $stmt->bind_param(
                'issdss', //integer, string,string, decimal , string,string
                $user_id,
                $type,
                $category,
                $amount,
                $description,
                $date  
                );

                if ($stmt->execute()){
                    $success_message = 'Záznam byl úspěšně uložen'; 
                } else {
                    $error_message = 'Něco se pokazilo, zkuste to znovu.';
                }
                $stmt -> close();
            } else {
                $error_message = "Chyba při přípravě dotazu do databáze";
            }


            } 
        
        }
    }
}

//Načte kategorie
$query_categories  = "SELECT * FROM categories ORDER BY type, name";
$result_categories = mysqli_query($conn, $query_categories);

$income_categories  = array();
$expense_categories = array();

while ($cat = mysqli_fetch_assoc($result_categories)) {
    if ($cat['type'] === 'income') {
        $income_categories[] = $cat;
    } else {
        $expense_categories[] = $cat;
    }
}

ob_start();
?>

<h1 class="page-title">Přidat záznam</h1>

<?php if ($success_message !== '') { ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php } ?>

<?php if ($error_message !== '') { ?>
    <div class="alert alert-error"><?php echo $error_message; ?></div>
<?php } ?>

<div class="form-card">
    <form method="POST" action="">

        <!--Výběr příjem nebo výdaj -->
        <div class="type-selector">
            <label class="type-option">
                <input type="radio" name="type" value="income" checked
                    <?php if (isset($_POST['type']) && $_POST['type'] === 'income') { echo 'checked'; } ?>>
                <p>Příjem</p>
            </label>
            <label class="type-option">
                <input type="radio" name="type" value="expense" 
                    <?php if (isset($_POST['type']) && $_POST['type'] === 'expense') { echo 'checked'; } ?>>
                <p>Výdaj</p>
            </label>
        </div>

        <!--Výběr kategorie-->
        <div class="form-group">
            <label for="category">Kategorie <span class="required">*</span></label>

            <select name="category" id="category-income">
                <?php foreach ($income_categories as $cat) { ?>
                    <option value="<?php echo $cat['name'];?>">
                        <?php echo $cat['name']; ?>
                    </option>
                <?php }?>
            </select>

            <select name="category" id="category-expense" style="display:none;">
                <?php foreach ($expense_categories as $cat) { ?>
                    <option value="<?php echo $cat['name'];?>">
                        <?php echo $cat['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!--Částka-->
        <div class="form-group">

            <label for="amount">Částka (Kč) <span class="required">*</span></label>
            <input type="number" name="amount" id="amount"
                   step="0.01" min="0" placeholder="0,00"
                   value="<?php if (isset($_POST['amount'])) { echo $_POST['amount']; } ?>">

        </div>

        <!--Datum-->
        <div class="form-group">
            <label for="date">Datum <span class="required">*</span></label>
            <input type="date" name="date" id="date" max="<?php echo date('Y-m-d'); ?>"
                   value="<?php if (isset($_POST['date'])) { echo $_POST['date']; } else { echo date('Y-m-d'); } ?>">
        </div>

        <!--Popis-->
        <div class="form-group">
            <label for="description">Popis</label>
            <input type="text" name="description" id="description"
                   placeholder="Volitelný popis záznamu..."
                   value="<?php if (isset($_POST['description'])) { echo $_POST['description']; } ?>">
        </div>

        <button type="submit" class="btn-submit">Uložit záznam</button>

    </form>
</div>

<?php
$page_content = ob_get_clean();
require_once 'layout.php';
?>