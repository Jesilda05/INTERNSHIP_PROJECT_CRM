<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check if the user is authenticated and is a 'Customer'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header('Location: ../login.php');
    exit();
}

$cust_id = (int)$_SESSION['user_id']; // Cast user_id to integer for security

// Prepare the SQL query to retrieve feedback data for the logged-in customer
$sql = "SELECT * FROM feedback WHERE customer_id = ? ORDER BY created_at DESC";
$prestmt = $conn->prepare($sql);
$prestmt->bind_param('i', $cust_id);

// Execute the prepared statement and check for errors
$prestmt->execute();
$res = $prestmt->get_result();

if ($conn->error) {
    echo "SORRY! We couldn't retrieve your data due to the following error.";
    error_log($conn->error); // Log the error for further analysis
}

?>

<?php include('header2.php'); ?>

<h2><b>Manage Feedback</b></h2> <!-- Corrected the closing tag -->

<table border="1">
    <thead>
        <tr>
            <th><strong>Feedback</strong></th>
            <th><strong>Created At</strong></th>
            <th><strong>Actions</strong></th>
        </tr>
    </thead>
    <tbody>
    <?php if ($res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['feedback']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                    <a href="edit_feedback2.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="delete_feedback2.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">NO FEEDBACK FOUND IN THE TABLE.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>

<?php
// Free up resources and close database connections
$res->close();
$prestmt->close();
$conn->close();
?>
