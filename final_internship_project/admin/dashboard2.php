<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure the session is active and valid for Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    // Destroy session if invalid and redirect to login
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Fetch totals
$totals = [
    'quotations' => 0,
    'tickets' => 0,
    'sales' => 0,
    'leads' => 0
];

$tables = [
    'quotations' => 'quotations',
    'tickets' => 'tickets',
    'sales' => 'sales',
    'leads' => 'leads'
];

foreach ($tables as $key => $table) {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $totals[$key] = $row['count'];
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare SQL: " . $conn->error); // Log any SQL errors
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css"> <!-- Adjust the path as needed -->
</head>
<body>
    <?php include('header2.php'); ?>

    <div class="dashboard">
        <h1>Admin Dashboard</h1>
        
        

        <div class="dashboard-stats">
            <div class="dashboard-stat-card">
                <h3>Total Quotations</h3>
                <p><?php echo htmlspecialchars($totals['quotations']); ?></p>
            </div>
            <div class="dashboard-stat-card">
                <h3>Total Tickets</h3>
                <p><?php echo htmlspecialchars($totals['tickets']); ?></p>
            </div>
            <div class="dashboard-stat-card">
                <h3>Total Sales</h3>
                <p><?php echo htmlspecialchars($totals['sales']); ?></p>
            </div>
            <div class="dashboard-stat-card">
                <h3>Total Leads</h3>
                <p><?php echo htmlspecialchars($totals['leads']); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
