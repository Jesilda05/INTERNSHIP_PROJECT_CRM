<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only Admin can access this page
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

$error = $success = '';
$lead_manager_id = (int)$_GET['id'];

if (isset($lead_manager_id) && filter_var($lead_manager_id, FILTER_VALIDATE_INT)) {
    $sql = "SELECT * FROM leadmanagers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $lead_manager_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lead_manager = $result->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

            if (empty($name) || empty($email)) {
                $error = "All fields are required.";
            } elseif (!preg_match('/^[a-zA-Z\s.]+$/', $name)) {
                $error = "Name can only contain letters, spaces, and basic punctuation.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $sql = "UPDATE leadmanagers SET name = ?, email = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssi', $name, $email, $lead_manager_id);

                if ($stmt->execute()) {
                    $success = "Lead Manager updated successfully.";
                    logUserActivity($_SESSION['user_id'], $_SESSION['role'], "edit lead manager");
                    header("Location: manage_lead_manager.php");
                    exit();
                } else {
                    $error = "Error updating Lead Manager: " . $stmt->error;
                }
            }
        }
    } else {
        $error = "Lead Manager not found.";
    }

    $stmt->close();
}
?>

<?php include('header2.php'); ?>

<div class="admin_container">
    <h3 class="form-heading">Edit Lead Manager</h3>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="edit_lead_manager.php?id=<?php echo $lead_manager_id; ?>" method="POST">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars($lead_manager['name']); ?>" required><br>
        Email: <input type="email" name="email" value="<?php echo htmlspecialchars($lead_manager['email']); ?>" required><br>
        <button type="submit">Update Lead Manager</button>
    </form>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
</div>


<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Tahoma, Geneva, sans-serif;
    }

    .admin_container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        text-align: center;
        background-color: #cc5e61;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border:4px solid black;

    }

    h3 {
        margin-bottom: 20px;
        font-size: 30px;
        color: black;
    }

    form {
        border: 4px solid black;
        padding: 20px;
        border-radius: 8px;
        background-color: white;
    }

    input[type="text"], input[type="email"] {
        width: calc(100% - 24px);
        padding: 10px;
        margin-bottom: 15px;
        border: 2px solid black;
        border-radius: 4px;
    }

    button {
        background-color:#cc5e61 ;
        color: black;
        padding: 10px;
        border: 2px solid black;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }

    button:hover {
        background-color: #e63c3c;
    }

    .error {
        color: black;
        margin-bottom: 20px;
    }

    .success {
        color: green;
        margin-top: 20px;
    }
</style>
