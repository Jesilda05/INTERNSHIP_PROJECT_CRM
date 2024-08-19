<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure that only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

$lead_manager_id = (int)$_SESSION['user_id'];
$sql = "SELECT * FROM leads WHERE lead_manager_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $lead_manager_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Include header -->
<?php include('header2.php'); ?>
<div class="container">
<h3>Manage Leads</h3>

<!-- Display leads in a table -->
<table border="1">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <a href="edit_lead2.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_lead2.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No leads found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>


<?php include('footer.php'); ?>

<?php
$stmt->close();
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
