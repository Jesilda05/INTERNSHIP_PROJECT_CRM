<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'SalesManager') {
    header('Location: ../login.php');
    exit();
}

// Get the Sales Manager ID
$sales_manager_id = (int)$_SESSION['user_id'];

// Fetch statistics for sales manager
$stats = [
    'total_sales' => 0,
    'total_sales_count' => 0
];

// Query to get total sales and sales count
$sql = "SELECT COUNT(*) AS total_sales_count, SUM(amount) AS total_sales FROM sales WHERE sales_manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sales_manager_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_sales_count'] = $row['total_sales_count'];
    $stats['total_sales'] = $row['total_sales'];
}
$stmt->close();

// Fetch recent sales
$recentSales = [];
$recentSalesSql = "SELECT id, amount, created_at FROM sales WHERE sales_manager_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recentSalesSql);
$stmt->bind_param('i', $sales_manager_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentSales[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/sales_styles.css"> <!-- Adjust path as needed -->
</head>
<body>
    <?php include('header2.php'); ?>

    <div class="dashboard">
        <h2>Sales Manager Dashboard</h2>

        <div class="dashboard-stats">
            <div class="dashboard-stat-card">
                <h3>Total Sales</h3>
                <p>$<?php echo number_format($stats['total_sales'], 2); ?></p>
            </div>
            <div class="dashboard-stat-card">
                <h3>Total Number of Sales</h3>
                <p><?php echo htmlspecialchars($stats['total_sales_count']); ?></p>
            </div>
        </div>

        <div class="recent-sales">
            <h2>Recent Sales</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentSales)): ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['id']); ?></td>
                                <td>$<?php echo number_format($sale['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($sale['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No recent sales found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
