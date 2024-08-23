<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$admin_id = (int)$_SESSION['user_id']; 

$sql = "SELECT t.id, t.customer_id, c.name AS customer_name, t.subject, t.description, t.created_at, t.status, t.response 
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        ORDER BY t.created_at DESC";

$prestmt = $conn->prepare($sql);
if (!$prestmt) {
    echo "Failed to prepare statement: " . $conn->error;
    exit();
}

$prestmt->execute();
$res = $prestmt->get_result();

if ($conn->error) {
    echo "Failed to retrieve tickets.";
    error_log($conn->error); 
    exit();
}
?>

<?php include('header2.php'); ?>

<div class="admin_container">
    <h3>Manage Tickets</h3>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="respond_ticket.php?id=<?php echo $row['id']; ?>" class="edit-link">Respond</a> |
                            <a href="edit_delete_respond_ticket.php?id=<?php echo $row['id']; ?>" class="edit-link">Edit/Delete Response</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No tickets found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
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