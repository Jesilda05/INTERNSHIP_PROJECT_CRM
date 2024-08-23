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
    $sql = "INSERT INTO user_logs (user_id, role, action, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('iss', $userId, $role, $action);
        $stmt->execute();
        $stmt->close();
    }
}

$admin_id = (int)$_SESSION['user_id']; 

if (!isset($_GET['id'])) {
    echo "Invalid Ticket ID!";
    exit();
}

$ticket_id = (int)$_GET['id'];

$sql = "SELECT t.id, t.customer_id, c.name AS customer_name, t.subject, t.description, t.created_at, t.response, t.response_date, t.status 
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        WHERE t.id = ?";
$prestmt = $conn->prepare($sql);
$prestmt->bind_param('i', $ticket_id);
$prestmt->execute();
$res = $prestmt->get_result();
if ($res->num_rows === 0) {
    echo "Ticket not found!";
    exit();
}

$ticket = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = trim($_POST['response']);
    $response_date = date('Y-m-d H:i:s');
    $status = 'Responded';

    $update_sql = "UPDATE tickets SET response = ?, response_date = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssi', $response, $response_date, $status, $ticket_id);
    if ($update_stmt->execute()) {
        header("Location: view_tickets_admin.php");
        logUserActivity($admin_id, $_SESSION['role'], "Responded to ticket ID $ticket_id");
        exit();
    } else {
        echo "Error: Unable to send the response.";
    }
}
?>

<?php include('header2.php'); ?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
    }
    .admin_container {
        max-width: 800px;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        border:4px solid black;

        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #f9f9f9;

    }
    th, td {
        padding: 12px;
        border: 1px solid #c36262;
    }
    th {
        background-color: #c36262; 
        color: white;
        padding: 12px;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #c36262;
        border-radius: 4px;
        box-sizing: border-box;
    }
    input[type="submit"] {
        background-color: #c36262;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: #003d79;
    }
</style>

<div class="admin_container">
    <h2><b>Respond to Ticket</b></h2>

    <table>
        <tr>
            <th>Customer Name</th>
            <td><?php echo htmlspecialchars($ticket['customer_name']); ?></td>
        </tr>
        <tr>
            <th>Subject</th>
            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo htmlspecialchars($ticket['status']); ?></td>
        </tr>
        <tr>
            <th>Response</th>
            <td><?php echo nl2br(htmlspecialchars($ticket['response'])); ?></td>
        </tr>
    </table>

    <form method="POST" action="">
        <label for="response"><strong>Your Response:</strong></label><br>
        <textarea name="response" id="response" rows="5" required><?php echo htmlspecialchars($ticket['response']); ?></textarea><br><br>
        <input type="submit" value="Send Response">
    </form>
</div>


<?php
$prestmt->close();
$conn->close();
?>
