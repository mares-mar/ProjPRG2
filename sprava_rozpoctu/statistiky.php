<?php
session_start();
$active_page = 'statistiky';

if (!isset($_SESSION['user_id'])) {
    header('Location: /sprava_rozpoctu/login.php');
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];

// Výdaje podle kategorie
$query_by_category = "SELECT category, SUM(amount) AS total 
                      FROM transactions 
                      WHERE type = 'expense' AND user_id = $user_id
                      GROUP BY category 
                      ORDER BY total DESC";
$result_by_category = mysqli_query($conn, $query_by_category);

$category_labels = array();
$category_totals = array();

while ($row = mysqli_fetch_assoc($result_by_category)) {
    $category_labels[] = $row['category'];
    $category_totals[] = $row['total'];
}

//Příjmy vs výdaje za měsíc
$query_monthly = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') AS month,
                    SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
                  FROM transactions
                  WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  AND date <=CURDATE()
                  AND user_id = $user_id
                  GROUP BY month
                  ORDER BY month ASC";
$result_monthly = mysqli_query($conn, $query_monthly);


$monthly_labels    = array();
$monthly_income    = array();
$monthly_expenses  = array();

while ($row = mysqli_fetch_assoc($result_monthly)) {
    //Převádění data do více čitelné formy
    $monthly_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_income[]   = $row['income'];
    $monthly_expenses[] = $row['expense'];
}

//Celkové součty pro příjmy a výdaje
$query_totals = "SELECT 
                    SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
                 FROM transactions
                 WHERE user_id = $user_id";
$result_totals = mysqli_query($conn, $query_totals);
$totals        = mysqli_fetch_assoc($result_totals);


$total_income = $totals["total_income"] ?? 0;

$total_expense = $totals["total_expense"] ?? 0;

//Předává data do Javascriptu jako json
$json_category_labels  = json_encode($category_labels,  JSON_UNESCAPED_UNICODE);
$json_category_totals  = json_encode($category_totals);
$json_monthly_labels   = json_encode($monthly_labels,   JSON_UNESCAPED_UNICODE);
$json_monthly_income   = json_encode($monthly_income);
$json_monthly_expenses = json_encode($monthly_expenses);

ob_start();
?>

<h1 class="page-title">Statistiky</h1>

<!--Celkové příjmy/výdaje -->
<div class="cards-grid">
    <div class="card card-income">
        <p class="card-label">Celkové příjmy</p>
        <p class="card-value"><?php echo number_format($total_income, 2, ',', ' '); ?> Kč</p>
    </div>
    <div class="card card-expense">
        <p class="card-label">Celkové výdaje</p>
        <p class="card-value"><?php echo number_format($total_expense, 2, ',', ' '); ?> Kč</p>
    </div>
</div>

<!--Grafy-->
<div class="charts-grid">
    <div class="chart-card">
        <h2 class="section-title">Příjmy vs výdaje (posledních 6 měsíců)</h2>
        <canvas id="monthlyChart"></canvas>
    </div>
    <div class="chart-card">
        <h2 class="section-title">Výdaje podle kategorií</h2>
        <canvas id="categoryChart"></canvas>
    </div>
</div>

<!--Neviditelné inputy na data pro Javascript -->
<input type="hidden" id="data-category-labels"  value='<?php echo $json_category_labels; ?>'>
<input type="hidden" id="data-category-totals"  value='<?php echo $json_category_totals; ?>'>
<input type="hidden" id="data-monthly-labels"   value='<?php echo $json_monthly_labels; ?>'>
<input type="hidden" id="data-monthly-income"   value='<?php echo $json_monthly_income; ?>'>
<input type="hidden" id="data-monthly-expenses" value='<?php echo $json_monthly_expenses; ?>'>

<?php
$page_content = ob_get_clean();
require_once 'layout.php';
?>