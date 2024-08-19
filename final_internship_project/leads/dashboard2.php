<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

// Get the Lead Manager ID
$lead_manager_id = (int)$_SESSION['user_id'];

// Fetch statistics (e.g., number of leads)
$stats = [
    'total_leads' => 0,
    'new_leads' => 0,
];

// Query to get total leads assigned to the Lead Manager
$sql = "SELECT COUNT(*) AS total FROM leads WHERE lead_manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $lead_manager_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_leads'] = $row['total'];
}

// Query to get the number of new leads (assuming 'status' of 'New' indicates new leads)
$sql = "SELECT COUNT(*) AS new FROM leads WHERE lead_manager_id = ? AND status = 'New'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $lead_manager_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['new_leads'] = $row['new'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/lead_styles.css"> <!-- Ensure path is correct -->
</head>
<body>
    <?php include('header2.php'); ?>

    <div class="dashboard">
        <h2>Lead Manager Dashboard</h2>

        <div class="dashboard-stats">
            <div class="dashboard-stat-card">
                <h3>Total Leads</h3>
                <p><?php echo htmlspecialchars($stats['total_leads']); ?></p>
            </div>  
            <div class="dashboard-stat-card">
                <h3>New Leads</h3>
                <p><?php echo htmlspecialchars($stats['new_leads']); ?></p>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
