<?php
session_start();
$active_page = 'historie';

if (!isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/login.php');
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];

//editování a ulození řádku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $edit_id     = intval($_POST['id']);
    $edit_type   = $_POST['type'];
    $edit_cat    = mysqli_real_escape_string($conn, $_POST['category']);
    $edit_amount = floatval($_POST['amount']);
    $edit_desc   = mysqli_real_escape_string($conn, $_POST['description']);
    $edit_date   = $_POST['date'];

    $query_update = "UPDATE transactions 
                     SET type        = '$edit_type',
                         category    = '$edit_cat',
                         amount      = '$edit_amount',
                         description = '$edit_desc',
                         date        = '$edit_date'
                     WHERE id = $edit_id AND user_id = $user_id";

    mysqli_query($conn, $query_update);

    //Pošle JSON odpoveď zpátky do javascriptu
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
    exit();
}

//smazání řádku
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $query_delete = "DELETE FROM transactions WHERE id = $delete_id AND user_id = $user_id";
    mysqli_query($conn, $query_delete);

    header('Location: /sprava_rozpoctu/historie.php');
    exit();
}

// filtrování
if (isset($_GET['type']) && $_GET['type'] !== '') {
    $filter_type = mysqli_real_escape_string($conn, $_GET['type']);
    $where = "WHERE type = '$filter_type' AND user_id = $user_id";
} else {
    $filter_type = '';
    $where = "WHERE user_id = $user_id";
}

//načtení všech transakcí
$query      = "SELECT * FROM transactions $where ORDER BY date DESC";
$result     = mysqli_query($conn, $query);
$total_rows = mysqli_num_rows($result);

ob_start();
?>

<h1 class="page-title">Historie záznamů</h1>

<!--Lišta filtrování -->
<div class="filter-bar">
    <span class="filter-label">Filtrovat:</span>
    <a href="/sprava_rozpoctu/historie.php"
       class="filter-btn <?php if ($filter_type === '') { echo 'active'; } ?>">
        Vše
    </a>
    <a href="/sprava_rozpoctu/historie.php?type=income"
       class="filter-btn <?php if ($filter_type === 'income') { echo 'active'; } ?>">
        Příjmy
    </a>
    <a href="/sprava_rozpoctu/historie.php?type=expense"
       class="filter-btn <?php if ($filter_type === 'expense') { echo 'active'; } ?>">
        Výdaje
    </a>
</div>


<p class="results-count">Nalezeno záznamů: <strong><?php echo $total_rows; ?></strong></p>

<!-- Tabulka všech transakcí -->
<table class="transactions-table">
    <thead>
        <tr>
            <th>Datum</th>
            <th>Popis</th>
            <th>Kategorie</th>
            <th>Typ</th>
            <th>Částka</th>
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($total_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rid = $row['id'];

                echo '<tr id="row-' . $rid . '">';

                echo '<td class="view-' . $rid . '">' . $row['date'] . '</td>';

                if ($row['description'] !== '') {
                    echo '<td class="view-' . $rid . '">' . $row['description'] . '</td>';
                } else {
                    echo '<td class="view-' . $rid . '" style="color:#aaa;">—</td>';
                }

                echo '<td class="view-' . $rid . '">' . $row['category'] . '</td>';

                if ($row['type'] === 'income') {
                    echo '<td class="view-' . $rid . '"><span class="badge badge-income">Příjem</span></td>';
                    echo '<td class="view-' . $rid . ' amount-income">+ ' . number_format($row['amount'], 2, ',', ' ') . ' Kč</td>';
                } else {
                    echo '<td class="view-' . $rid . '"><span class="badge badge-expense">Výdaj</span></td>';
                    echo '<td class="view-' . $rid . ' amount-expense">- ' . number_format($row['amount'], 2, ',', ' ') . ' Kč</td>';
                }

                // editování (schováno defaultně)
                echo '<td class="edit-' . $rid . '" style="display:none;">';
                echo '<input class="inline-input" id="edit-date-' . $rid . '" type="date" value="' . $row['date'] . '">';
                echo '</td>';

                echo '<td class="edit-' . $rid . '" style="display:none;">';
                echo '<input class="inline-input" id="edit-desc-' . $rid . '" type="text" value="' . $row['description'] . '">';
                echo '</td>';

                echo '<td class="edit-' . $rid . '" style="display:none;">';
                echo '<input class="inline-input" id="edit-cat-' . $rid . '" type="text" value="' . $row['category'] . '">';
                echo '</td>';

                echo '<td class="edit-' . $rid . '" style="display:none;">';
                echo '<select class="inline-input" id="edit-type-' . $rid . '">';
                echo '<option value="income"'  . ($row['type'] === 'income'  ? ' selected' : '') . '>Příjem</option>';
                echo '<option value="expense"' . ($row['type'] === 'expense' ? ' selected' : '') . '>Výdaj</option>';
                echo '</select>';
                echo '</td>';

                echo '<td class="edit-' . $rid . '" style="display:none;">';
                echo '<input class="inline-input" id="edit-amount-' . $rid . '" type="number" step="0.01" min="0"value="' . $row['amount'] . '">';
                echo '</td>';

                // tlačítka smazat a upravit
                echo '<td>';
                echo '<button class="btn-edit"   onclick="startEdit(' . $rid . ')">Upravit</button>';
                echo '<button class="btn-save"   onclick="saveEdit(' . $rid . ')"   style="display:none;">Uložit</button>';
                echo '<button class="btn-cancel" onclick="cancelEdit(' . $rid . ')" style="display:none;">Zrušit</button>';
                echo '<a href="/sprava_rozpoctu/historie.php?delete_id=' . $rid . '"
                         class="btn-delete"
                         onclick="return confirm(\'Opravdu chcete smazat tento záznam?\')">Smazat</a>';
                echo '</td>';

                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:24px;">Žádné záznamy</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$page_content = ob_get_clean();
require_once 'layout.php';
?>