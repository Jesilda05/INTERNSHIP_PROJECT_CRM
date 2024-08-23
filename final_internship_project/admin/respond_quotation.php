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

$admin_id = (int)$_SESSION['user_id']; 

if (!isset($_GET['id'])) {
    echo "Invalid Quotation ID!";
    exit();
}

$quot_id = (int)$_GET['id'];

$sql = "SELECT q.id, q.customer_id, c.name AS customer_name, q.product, q.details, q.created_at, q.response, q.response_date, q.status 
        FROM quotations q
        JOIN customers c ON q.customer_id = c.id
        WHERE q.id = ?";
$prestmt = $conn->prepare($sql);

if (!$prestmt) {
    echo "Failed to prepare statement: " . $conn->error;
    exit();
}

$prestmt->bind_param('i', $quot_id);
$prestmt->execute();
$res = $prestmt->get_result();

if ($res->num_rows === 0) {
    echo "Quotation not found!";
    exit();
}

$quotation = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = trim($_POST['response']);
    $response_date = date('Y-m-d H:i:s');
    $status = 'Responded';

    $update_sql = "UPDATE quotations SET response = ?, response_date = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);

    if (!$update_stmt) {
        echo "Failed to prepare update statement: " . $conn->error;
        exit();
    }

    $update_stmt->bind_param('sssi', $response, $response_date, $status, $quot_id);
    
    if ($update_stmt->execute()) {
        echo "Response sent successfully!";
        header("Location: view_quotations_admin.php"); 
        logUserActivity($_SESSION['user_id'], $_SESSION['role'], "respond to quotations ");
        exit();
    } else {
        echo "Error: Unable to send the response.";
        error_log($conn->error); 
    }
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
        
        <input type="submit" value="Send Response" class="submit-button">
    </form>
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

        box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
    }

    h2 {
        font-size: 28px;
        color: #333;
        margin-bottom: 20px;
        text-align: left; 

    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #f9f9f9; 

    }

    .styled-table, .styled-table th, .styled-table td {
        border: 1px solid #c36262; 
    }

    .styled-table th {
        background-color: #c36262; 
        color: white;
        padding: 12px;
    }

    .styled-table td {
        padding: 12px;
    }

    .styled-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .response-form {
        margin-top: 20px;
    }

    .response-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #c36262;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .response-form .submit-button {
        background-color: #c36262;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 10px 0;
        cursor: pointer;
        border-radius: 4px;
    }

    .response-form .submit-button:hover {
        background-color: #003d79;
    }
</style>
