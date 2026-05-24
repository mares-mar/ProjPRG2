<?php
session_start();
$active_page = 'dashboard';
require_once 'config.php';

$user_id = $_SESSION['user_id'];

//součet všech příjmů
$query_income  = "SELECT SUM(amount) AS total FROM transactions 
                  WHERE type = 'income' AND user_id = $user_id";
$result_income = mysqli_query($conn, $query_income);
$row_income    = mysqli_fetch_assoc($result_income);

$total_income = $row_income['total'] ?? 0;

// Součet všech výdajů
$query_expense  = "SELECT SUM(amount) AS total FROM transactions 
                   WHERE type = 'expense' AND user_id = $user_id";
$result_expense = mysqli_query($conn, $query_expense);
$row_expense    = mysqli_fetch_assoc($result_expense);

$total_expense = $row_expense['total'] ?? 0;

// výpočet bilance
$balance = $total_income - $total_expense;

// získá posledních 5 záznamů
$query_recent  = "SELECT * FROM transactions 
                  WHERE user_id = $user_id 
                  ORDER BY date DESC 
                  LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);

ob_start();
?>

<h1 class="page-title">Přehled financí</h1>

<!--Kartičky-->
<div class="cards-grid">
    <div class="card card-income">
        <p class="card-label">Celkové příjmy</p>
        <p class="card-value"><?php echo number_format($total_income, 2, ',', ' '); ?> Kč</p>
    </div>
    <div class="card card-expense">
        <p class="card-label">Celkové výdaje</p>
        <p class="card-value"><?php echo number_format($total_expense, 2, ',', ' '); ?> Kč</p>
    </div>
    <div class="card card-balance">
        <p class="card-label">Zůstatek</p>
        <p class="card-value"><?php echo number_format($balance, 2, ',', ' '); ?> Kč</p>
    </div>
</div>

<!--Tabulka na posledních 5 transakcí-->
<h2 class="section-title">Poslední záznamy</h2>

<table class="transactions-table">
    <thead>
        <tr>
            <th>Datum</th>
            <th>Popis</th>
            <th>Kategorie</th>
            <th>Typ</th>
            <th>Částka</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result_recent) > 0) {
            while ($row = mysqli_fetch_assoc($result_recent)) {
                echo '<tr>';
                echo '<td>' . $row['date'] . '</td>';

                if ($row['description'] !== '') {
                    echo '<td>' . $row['description'] . '</td>';
                } else {
                    echo '<td style="color:#aaa;">—</td>';
                }

                echo '<td>' . $row['category'] . '</td>';

                if ($row['type'] === 'income') {
                    echo '<td><span class="badge badge-income">Příjem</span></td>';
                    echo '<td class="amount-income">+ ' . number_format($row['amount'], 2, ',', ' ') . ' Kč</td>';

                } else {
                    echo '<td><span class="badge badge-expense">Výdaj</span></td>';
                    echo '<td class="amount-expense">- ' . number_format($row['amount'], 2, ',', ' ') . ' Kč</td>';
                    
                }

                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5" style="text-align:center;color:#aaa;">Zatím žádné záznamy</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$page_content = ob_get_clean();
require_once 'layout.php';
?>