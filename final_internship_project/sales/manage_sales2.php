<?php
session_start();
include('../mainconn/db_connect.php');

// Check for user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'SalesManager') {
    header('Location: ../login.php');
    exit();
}

// Get Sales Manager ID and cast it to int
$sales_manager_id = (int)$_SESSION['user_id'];

// Prepare SQL query to retrieve all sales records for the Sales Manager
$sql = "SELECT * FROM sales WHERE sales_manager_id = ? ORDER BY created_at DESC";
$prestmt = $conn->prepare($sql);
$prestmt->bind_param('i', $sales_manager_id);

// Execute the statement
$prestmt->execute();
$res = $prestmt->get_result();

// Error handling
if ($conn->error) {
    echo "SORRY! We couldn't retrieve your data due to the following error.";
    error_log($conn->error);
}

?>

<?php include('header2.php'); ?>
<div class="container">
<h3>Manage Sales</h3>

<table border="1">
    <thead>
        <tr>
            <th><strong>Amount</strong></th>
            <th><strong>Created At</strong></th>
            <th><strong>Actions</strong></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <a href="edit_sales2.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_sales2.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">NO SALES RECORDS FOUND IN THE TABLE.</td>
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
    .container {
        max-width: 800px; /* Limit container width */
        margin: 40px auto; /* Center container horizontally and add top margin */
        padding: 20px; /* Add padding around the container */
        text-align: center; /* Center-align text within the container */
        border: 5px solid black; /* Light border around the container */
        border-radius: 8px; /* Rounded corners */
        background-color: #f9f9f9; /* Light background color */
    }

    h3 {
        margin-bottom: 20px; /* Space between heading and table */
        font-size: 24px;
        color: #007BFF; /* Heading color */
    }

    table {
        width: 100%; /* Full-width table */
        border-collapse: collapse; /* Collapse table borders */
        margin-top: 20px; /* Space above table */

    }

    th, td {
        border: 2px solid black; /* Light border for table cells */
        padding: 10px; /* Padding inside cells */
        text-align: left; /* Align text to the left */
        background-color: lightblue; /* Light background color */

    }

    th {
        background-color: #007BFF; /* Blue background for header */
        color: white; /* White text for header */
    }

    tr:nth-child(even) {
        background-color: #f2f2f2; /* Alternating row colors */
    }

    a.edit-link, a.delete-link {
        color: #007BFF; /* Link color */
        text-decoration: none; /* Remove underline */
    }

    a.edit-link:hover, a.delete-link:hover {
        text-decoration: underline; /* Underline on hover */
    }

    a.delete-link {
        color: #d9534f; /* Red color for delete link */
    }
</style>

