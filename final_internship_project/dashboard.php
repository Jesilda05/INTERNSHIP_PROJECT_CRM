<?php
session_start();
include('mainconn/db_connect.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard</title>
    <link rel="stylesheet" href="assets/styles.css"> 
</head>
<body>
    <header>
        <h1>Welcome to the Main Dashboard</h1>
        <nav>
            <ul>
                <?php if ($role === 'Admin'): ?>
                    <li><a href="admin/dashboard2.php">Admin Dashboard</a></li>
                <?php elseif ($role === 'SalesManager'): ?>
                    <li><a href="sales/sales_dashboard2.php">Sales Manager Dashboard</a></li>
                <?php elseif ($role === 'LeadManager'): ?>
                    <li><a href="leads/dashboard2.php">Lead Manager Dashboard</a></li>
                <?php elseif ($role === 'Customer'): ?>
                    <li><a href="customers/cust_dashboard2.php">Customer Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="logout.php">Logout</a>
    </header>

    <main>
        <h2>Navigation</h2>
        <p>Choose your dashboard from the links below:</p>
        <ul>
            <?php if ($role === 'Admin'): ?>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <?php elseif ($role === 'SalesManager'): ?>
                <li><a href="sales/sales_dashboard2.php">Sales Manager Dashboard</a></li>
            <?php elseif ($role === 'LeadManager'): ?>
                <li><a href="lead_manager_dashboard.php">Lead Manager Dashboard</a></li>
            <?php elseif ($role === 'Customer'): ?>
                <li><a href="customers/cust_dashboard2.php">Customer Dashboard</a></li>
            <?php endif; ?>
        </ul>
    </main>

    <footer>
        <p>&copy; 2024 Your Company Name</p>
    </footer>
</body>
</html>
