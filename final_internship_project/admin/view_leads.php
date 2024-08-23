<?php
session_start();
include('../mainconn/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT lead_manager_id, COUNT(id) AS total_leads
        FROM leads
        GROUP BY lead_manager_id
        ORDER BY total_leads DESC
        LIMIT 1";  
$prestmt = $conn->prepare($sql);
$prestmt->execute();
$res = $prestmt->get_result();

if ($conn->error) {
    echo "SORRY! We couldn't retrieve your data due to the following error.";
    error_log($conn->error);
}

?>

<?php include('header2.php'); ?>
<div class="admin_container">
<h3>Lead Manager with Highest Leads</h3>

<table border="1">
    <thead>
        <tr>
            <th><strong>Lead Manager ID</strong></th>
            <th><strong>Total Leads Created</strong></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['lead_manager_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_leads']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">NO LEAD MANAGER FOUND.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$res->close();
$prestmt->close();
$conn->close();
?>

<style>
    .admin_container {
        max-width: 800px; 
        margin: 40px auto; 
        padding: 20px; 
        text-align: center; 
        border: 5px solid black; 
        border-radius: 8px; 
        background-color: #cc5e61; 
    }

    h3 {
        margin-bottom: 20px; 
        font-size: 24px;
        color: white; 
    }

    table {
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 20px; 
    }

    th, td {
        border: 4px solid black; 
        padding: 10px; 
        text-align: left; 
        background-color: white; 
    }

    th {
        background-color: #e63c3c; 
        color: white; 
    }

    tr:nth-child(even) {
        background-color: #f16f6f; 
    }

    a.edit-link, a.delete-link {
        color: grey; 
        text-decoration: none; 
    }

    a.edit-link:hover, a.delete-link:hover {
        text-decoration: underline; 
    }

    a.delete-link {
        color: #d9534f; 
    }
</style>
