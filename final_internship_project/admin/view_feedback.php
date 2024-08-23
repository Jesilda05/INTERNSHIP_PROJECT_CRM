<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT f.id, f.customer_id, c.name AS customer_name, f.feedback, f.created_at 
        FROM feedback f
        JOIN customers c ON f.customer_id = c.id
        ORDER BY f.created_at DESC";
$prestmt = $conn->prepare($sql);

$prestmt->execute();
$res = $prestmt->get_result();

if ($conn->error) {
    echo "SORRY! We couldn't retrieve the data due to the following error.";
    error_log($conn->error); 
}
?>

<?php include('header2.php'); ?>

<div class="admin_container">
    <h2><b>Manage Feedback</b></h2>

    <table>
        <thead>
            <tr>
                <th><strong>Customer Name</strong></th>
                <th><strong>Feedback</strong></th>
                <th><strong>Created At</strong></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['feedback']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">NO FEEDBACK FOUND IN THE TABLE.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


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
