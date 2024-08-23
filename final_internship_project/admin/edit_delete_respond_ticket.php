<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$admin_id = (int)$_SESSION['user_id']; 

if (isset($_GET['delete_id'])) {
    $ticket_id = (int)$_GET['delete_id'];

    $delete_sql = "UPDATE tickets SET response = NULL, response_date = NULL, status = 'Pending' WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $ticket_id);

    if ($delete_stmt->execute()) {
        header("Location: view_tickets_admin.php");
        exit();
    } else {
        echo "Error deleting response.";
    }
    $delete_stmt->close();
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ticket_id === 0) {
    echo "Invalid Ticket ID!";
    exit();
}

$sql = "SELECT t.id, t.customer_id, c.name AS customer_name, t.subject, t.description, t.created_at, t.response, t.response_date, t.status 
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        WHERE t.id = ?";
$prestmt = $conn->prepare($sql);
$prestmt->bind_param('i', $ticket_id);
$prestmt->execute();
$result = $prestmt->get_result();

if ($result->num_rows === 0) {
    echo "Ticket not found!";
    exit();
}

$ticket = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = trim($_POST['response']);
    $response_date = date('Y-m-d H:i:s');
    $status = 'Responded';

    $update_sql = "UPDATE tickets SET response = ?, response_date = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssi', $response, $response_date, $status, $ticket_id);

    if ($update_stmt->execute()) {
        header("Location: view_tickets_admin.php");
        exit();
    } else {
        echo "Error: Unable to update the response.";
    }
    $update_stmt->close();
}
?>

<?php include('header2.php'); ?>

<style>
    .ticket-container {
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


<div class="ticket-container">
    <h2><b>Respond to Ticket</b></h2>

    <table class="styled-table">
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

    <div class="response-form">
        <form method="POST">
            <label for="response">Your Response:</label><br>
            <textarea name="response" id="response" rows="5" required><?php echo htmlspecialchars($ticket['response']); ?></textarea><br>
            <input type="submit" value="Save Response" class="submit-button">
        </form>
    </div>

    <?php if (!empty($ticket['response'])): ?>
        <form method="GET">
            <input type="hidden" name="delete_id" value="<?php echo $ticket['id']; ?>">
            <input type="submit" value="Delete Response" class="delete-button" onclick="return confirm('Are you sure you want to delete this response?');">
        </form>
    <?php endif; ?>
</div>


<?php
$prestmt->close();
$conn->close();
?>
