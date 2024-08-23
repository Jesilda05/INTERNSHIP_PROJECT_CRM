<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

function logUserActivity($userId, $role, $action) {
    global $conn;
    error_log("Inside logUserActivity for user_id: $userId, role: $role, action: $action");

    $sql = "INSERT INTO user_logs (user_id, role, action, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error preparing statement for logging user activity: " . $conn->error);
        return;
    }

    $stmt->bind_param('iss', $userId, $role, $action);
    $stmt->execute();

    if ($stmt->error) {
        error_log("Error executing statement for logging user activity: " . $stmt->error);
    } else {
        error_log("Log entry created successfully for action: $action");
    }

    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $quotation_id = (int)$_GET['delete_id'];

    $delete_sql = "UPDATE quotations SET response = NULL, response_date = NULL, status = 'Pending' WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);

    if (!$delete_stmt) {
        error_log("Error preparing statement for deletion: " . $conn->error);
        echo "Error preparing statement for deletion.";
    }

    $delete_stmt->bind_param('i', $quotation_id);
    
    if ($delete_stmt->execute()) {
        echo "Response deleted successfully!";
        logUserActivity($_SESSION['user_id'], $_SESSION['role'], "Deleted response for quotation ID: $quotation_id");
        header("Location: view_quotations_admin.php");
        exit();
    } else {
        error_log("Error executing statement for deletion: " . $delete_stmt->error);
        echo "Error deleting response.";
    }

    $delete_stmt->close();
}

if (!isset($_GET['id'])) {
    echo "Invalid Quotation ID!";
    exit();
}

$quotation_id = (int)$_GET['id'];
$sql = "SELECT q.id, q.customer_id, c.name AS customer_name, q.product, q.details, q.created_at, q.response, q.response_date, q.status 
        FROM quotations q
        JOIN customers c ON q.customer_id = c.id
        WHERE q.id = ?";
$prestmt = $conn->prepare($sql);
$prestmt->bind_param('i', $quotation_id);
$prestmt->execute();
$result = $prestmt->get_result();

if ($result->num_rows === 0) {
    echo "Quotation not found!";
    exit();
}

$quotation = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = trim($_POST['response']);
    $response_date = date('Y-m-d H:i:s');
    $status = 'Responded';

    $update_sql = "UPDATE quotations SET response = ?, response_date = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssi', $response, $response_date, $status, $quotation_id);
    
    if ($update_stmt->execute()) {
        echo "Response updated successfully!";
        logUserActivity($_SESSION['user_id'], $_SESSION['role'], "Responded to quotation ID: $quotation_id");
        header("Location: view_quotations_admin.php");
        exit();
    } else {
        echo "Error: Unable to update the response.";
    }
    $update_stmt->close();
}

?>

<?php include('header2.php'); ?>

<div class="quotation-container">
    <h2><b>Respond to Quotation</b></h2>

    <table class="styled-table">
        <tr>
            <th>Customer Name</th>
            <td><?php echo htmlspecialchars($quotation['customer_name']); ?></td>
        </tr>
        <tr>
            <th>Product</th>
            <td><?php echo htmlspecialchars($quotation['product']); ?></td>
        </tr>
        <tr>
            <th>Details</th>
            <td><?php echo nl2br(htmlspecialchars($quotation['details'])); ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?php echo htmlspecialchars($quotation['created_at']); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo htmlspecialchars($quotation['status']); ?></td>
        </tr>
    </table>

    <form method="POST" action="" class="response-form">
        <label for="response">Your Response:</label><br>
        <textarea name="response" id="response" rows="5" cols="50" required><?php echo htmlspecialchars($quotation['response']); ?></textarea><br><br>
        
        <input type="submit" value="Save Response" class="submit-button">
    </form>

    <?php if (!empty($quotation['response'])): ?>
        <form method="GET" action="">
            <input type="hidden" name="delete_id" value="<?php echo $quotation['id']; ?>">
            <input type="submit" value="Delete Response" class="delete-button" onclick="return confirm('Are you sure you want to delete this response?');">
        </form>
    <?php endif; ?>
</div>


<?php
$prestmt->close();
$conn->close();
?>

<style>
    .quotation-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        border:4px solid black;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 26px;
        color: #333;
        margin-bottom: 20px;
        text-align: left; 
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 18px;
        text-align: left;
        background-color: #f9f9f9; 
    }

    .styled-table th, .styled-table td {
        padding: 12px 15px;
        border: 1px solid #c36262; 
    }

    .styled-table th {
        background-color: #c36262; 
        color: white;
        text-align: left;
    }

    .styled-table tr:nth-of-type(even) {
        background-color: #f2f2f2;
    }

    .styled-table tr:hover {
        background-color: #f1f1f1;
    }

    .response-form {
        margin-top: 20px;
    }

    textarea {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #c36262; 
        border-radius: 5px;
        resize: vertical;
    }

    .submit-button, .delete-button {
        padding: 10px 20px;
        font-size: 16px;
        color: white;
        background-color: #c36262; 
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        display: block;
        width: 100%;
        text-align: center;
    }

    .delete-button {
        background-color: #d9534f;
        margin-top: 10px;
    }

    .submit-button:hover, .delete-button:hover {
        opacity: 0.9;
    }
</style>
