<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT q.id, q.customer_id, c.name AS customer_name, q.product, q.details, q.created_at, q.status 
        FROM quotations q
        JOIN customers c ON q.customer_id = c.id
        ORDER BY q.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include('header2.php'); ?>

<div class="admin_container">
    <h3>Manage Quotations</h3>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Details</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['product']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['details'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="respond_quotation.php?id=<?php echo $row['id']; ?>" class="resp-link">Respond</a> |
                            <a href="edit_delete_respond_quotation.php?id=<?php echo $row['id']; ?>" class="edit-link">Edit Or Delete</a> |
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No quotations found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
$stmt->close();
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

    a.resp-link, a.edit-link {
        color: grey; 
        text-decoration: none; 
    }

    a.resp-link:hover, a.edit-link:hover {
        text-decoration: underline; 
    }

    a.edit-link {
        color: #d9534f; 
    }
</style>
